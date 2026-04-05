<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\BookingSlotService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private MidtransService $midtrans,
        private BookingSlotService $bookingSlotService,
    ) {}

    /**
     * Endpoint yang dipanggil Midtrans setelah transaksi berubah status.
     * Harus bisa diakses tanpa auth (dikecualikan dari CSRF & auth middleware).
     */
    public function handle(Request $request)
    {
        try {
            $notification = $this->midtrans->parseNotification();

            $orderId           = $notification->order_id;
            $paymentStatus     = $this->midtrans->resolvePaymentStatus($notification);
            $transactionId     = $notification->transaction_id ?? null;
            $paymentType       = $notification->payment_type   ?? null;
            $vaNumber          = $this->extractVaNumber($notification);
            $paidAt            = $paymentStatus === 'paid' ? now() : null;

            Log::info('Midtrans webhook received', [
                'order_id'       => $orderId,
                'status'         => $paymentStatus,
                'transaction_id' => $transactionId,
            ]);

            DB::transaction(function () use (
                $orderId, $paymentStatus, $transactionId,
                $paymentType, $vaNumber, $paidAt, $notification
            ) {
                // Update tabel payments
                $updated = DB::table('payments')
                    ->where('payment_code', $orderId)
                    ->update([
                        'status'                 => $paymentStatus,
                        'gateway_transaction_id' => $transactionId,
                        'gateway_response'       => json_encode($notification),
                        'va_number'              => $vaNumber,
                        'paid_at'                => $paidAt,
                        'updated_at'             => now(),
                    ]);

                if (! $updated) {
                    Log::warning('Midtrans webhook: payment not found', ['order_id' => $orderId]);
                    return;
                }

                // Sinkronisasi status booking
                if ($paymentStatus === 'paid') {
                    DB::table('bookings')
                        ->where('booking_code', $orderId)
                        ->where('status', '!=', 'cancelled')
                        ->update(['status' => 'confirmed', 'updated_at' => now()]);
                } elseif (in_array($paymentStatus, ['failed', 'expired'])) {
                    DB::table('bookings')
                        ->where('booking_code', $orderId)
                        ->whereIn('status', ['pending', 'waiting_payment'])
                        ->update(['status' => 'cancelled', 'updated_at' => now()]);
                }

                $this->bookingSlotService->syncBookedSlotsForBookingCode($orderId);
            });

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Midtrans webhook error: ' . $e->getMessage(), [
                'payload' => $request->all(),
            ]);

            // Tetap return 200 agar Midtrans tidak retry terus-menerus
            // kecuali signature invalid (kemungkinan request palsu)
            if (str_contains($e->getMessage(), 'signature')) {
                return response()->json(['status' => 'invalid signature'], 403);
            }

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Ekstrak nomor VA dari berbagai format response Midtrans.
     */
    private function extractVaNumber(object $notification): ?string
    {
        // Format VA dari bank (BCA, BNI, BRI, Mandiri, Permata)
        if (! empty($notification->va_numbers) && is_array($notification->va_numbers)) {
            return $notification->va_numbers[0]->va_number ?? null;
        }

        // Mandiri bill payment
        if (! empty($notification->bill_key)) {
            return $notification->bill_key;
        }

        return null;
    }
}
