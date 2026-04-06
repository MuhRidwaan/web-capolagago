<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        // Prioritaskan key dari database, fallback ke config/.env
        Config::$serverKey    = PaymentSetting::get('midtrans_server_key', config('midtrans.server_key'));
        Config::$clientKey    = PaymentSetting::get('midtrans_client_key', config('midtrans.client_key'));
        Config::$isProduction = (bool) PaymentSetting::get('midtrans_is_production', config('midtrans.is_production'));
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = (bool) PaymentSetting::get('midtrans_is_3ds', config('midtrans.is_3ds'));
    }

    /**
     * Buat Snap Token untuk ditampilkan di frontend.
     * Dipanggil saat user checkout dan memilih metode pembayaran.
     *
     * @param  array  $payload  Data transaksi (lihat buildPayload)
     * @return string  Snap token
     */
    public function createSnapToken(array $payload): string
    {
        return Snap::getSnapToken($payload);
    }

    /**
     * Buat Snap Token langsung dari data booking.
     * Ini shortcut yang akan dipakai controller nanti.
     *
     * @param  string  $orderId      booking_code dari tabel bookings
     * @param  int     $grossAmount  total_amount dalam rupiah (integer)
     * @param  array   $customerDetails  nama, email, phone user
     * @param  array   $itemDetails  array item booking
     * @return string  Snap token
     */
    public function createTokenFromBooking(
        string $orderId,
        int $grossAmount,
        array $customerDetails,
        array $itemDetails = []
    ): string {
        $payload = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => $customerDetails,
            'item_details'     => $itemDetails,
            'callbacks' => [
                'finish' => config('app.url') . '/booking/finish',
            ],
        ];

        return $this->createSnapToken($payload);
    }

    /**
     * Parse dan validasi notifikasi webhook dari Midtrans.
     * Gunakan di WebhookController.
     *
     * @return object  Notification object dari Midtrans SDK
     * @throws \Exception jika signature key tidak valid
     */
    public function parseNotification(): object
    {
        $notification = new Notification();

        // Validasi signature key
        $serverKey        = PaymentSetting::get('midtrans_server_key', config('midtrans.server_key'));
        $orderId          = $notification->order_id;
        $statusCode       = $notification->status_code;
        $grossAmount      = $notification->gross_amount;
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($notification->signature_key !== $expectedSignature) {
            throw new \Exception('Invalid Midtrans signature key.');
        }

        return $notification;
    }

    /**
     * Tentukan status pembayaran internal berdasarkan notifikasi Midtrans.
     *
     * @param  object  $notification
     * @return string  'paid' | 'pending' | 'failed' | 'expired' | 'refunded'
     */
    public function resolvePaymentStatus(object $notification): string
    {
        $transactionStatus = $notification->transaction_status;
        $fraudStatus       = $notification->fraud_status ?? null;

        return match (true) {
            $transactionStatus === 'capture' && $fraudStatus === 'accept' => 'paid',
            $transactionStatus === 'settlement'                           => 'paid',
            $transactionStatus === 'pending'                              => 'pending',
            $transactionStatus === 'deny'                                 => 'failed',
            $transactionStatus === 'expire'                               => 'expired',
            $transactionStatus === 'cancel'                               => 'failed',
            $transactionStatus === 'refund'                               => 'refunded',
            default                                                       => 'pending',
        };
    }

    /**
     * Cek status transaksi langsung ke Midtrans API.
     * Berguna untuk reconciliation atau cek manual.
     *
     * @param  string  $orderId  booking_code
     * @return object
     */
    public function checkStatus(string $orderId): object
    {
        return Transaction::status($orderId);
    }

    /**
     * Cancel transaksi di Midtrans.
     *
     * @param  string  $orderId
     * @return object
     */
    public function cancelTransaction(string $orderId): object
    {
        return Transaction::cancel($orderId);
    }
}
