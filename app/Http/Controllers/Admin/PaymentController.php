<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BookingSlotService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(
        private MidtransService $midtrans,
        private BookingSlotService $bookingSlotService,
    ) {}

    private function mitraId(): ?int
    {
        $user = auth()->user();
        return $user->hasRole('Super Admin') ? null : $user->mitraProfile?->id;
    }

    private function mitraBookingIds(?int $mitraId): ?array
    {
        if (! $mitraId) return null;

        return DB::table('booking_items as bi')
            ->join('products as p', 'p.id', '=', 'bi.product_id')
            ->where('p.mitra_id', $mitraId)
            ->pluck('bi.booking_id')
            ->unique()
            ->values()
            ->all();
    }

    public function index(Request $request)
    {
        $mitraId    = $this->mitraId();
        $bookingIds = $this->mitraBookingIds($mitraId);

        $query = DB::table('payments as py')
            ->join('bookings as b', 'b.id', '=', 'py.booking_id')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->leftJoin('payment_methods as pm', 'pm.id', '=', 'py.payment_method_id')
            ->select(
                'py.id', 'py.payment_code', 'py.amount', 'py.fee_amount',
                'py.status', 'py.paid_at', 'py.created_at',
                'py.gateway_transaction_id', 'py.va_number',
                'b.booking_code', 'b.id as booking_id',
                'u.name as user_name', 'u.email as user_email',
                'pm.name as method_name', 'pm.type as method_type'
            )
            ->when($bookingIds !== null, fn($q) => $q->whereIn('py.booking_id', $bookingIds))
            ->orderByDesc('py.created_at');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($w) => $w
                ->where('py.payment_code', 'like', "%$q%")
                ->orWhere('b.booking_code', 'like', "%$q%")
                ->orWhere('u.name', 'like', "%$q%")
                ->orWhere('py.gateway_transaction_id', 'like', "%$q%")
            );
        }

        if ($request->filled('status')) {
            $query->where('py.status', $request->status);
        }

        if ($request->filled('method_type')) {
            $query->where('pm.type', $request->method_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('py.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('py.created_at', '<=', $request->date_to);
        }

        $payments = $query->paginate(15)->withQueryString();

        $statusCounts = DB::table('payments')
            ->when($bookingIds !== null, fn($q) => $q->whereIn('booking_id', $bookingIds))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalPaid = DB::table('payments')
            ->where('status', 'paid')
            ->when($bookingIds !== null, fn($q) => $q->whereIn('booking_id', $bookingIds))
            ->sum('amount');

        return view('backend.payments.index', compact('payments', 'statusCounts', 'totalPaid'));
    }

    public function show(int $id)
    {
        $mitraId    = $this->mitraId();
        $bookingIds = $this->mitraBookingIds($mitraId);

        $payment = DB::table('payments as py')
            ->join('bookings as b', 'b.id', '=', 'py.booking_id')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->leftJoin('payment_methods as pm', 'pm.id', '=', 'py.payment_method_id')
            ->select(
                'py.*',
                'b.booking_code', 'b.visit_date', 'b.total_amount as booking_total',
                'b.status as booking_status', 'b.id as booking_id',
                'u.name as user_name', 'u.email as user_email',
                'pm.name as method_name', 'pm.type as method_type', 'pm.provider'
            )
            ->when($bookingIds !== null, fn($q) => $q->whereIn('py.booking_id', $bookingIds))
            ->where('py.id', $id)
            ->first();

        abort_if(! $payment, 404);

        return view('backend.payments.detail', compact('payment'));
    }

    /**
     * Konfirmasi pembayaran manual (transfer bank).
     */
    public function confirm(Request $request, int $id)
    {
        $payment = DB::table('payments')->where('id', $id)->first();
        abort_if(! $payment, 404);
        abort_if($payment->status !== 'pending', 422, 'Hanya pembayaran pending yang bisa dikonfirmasi.');

        DB::transaction(function () use ($payment, $request) {
            DB::table('payments')->where('id', $payment->id)->update([
                'status'     => 'paid',
                'paid_at'    => now(),
                'updated_at' => now(),
            ]);

            DB::table('bookings')->where('id', $payment->booking_id)->update([
                'status'     => 'confirmed',
                'updated_at' => now(),
            ]);

            $this->bookingSlotService->syncBookedSlotsForBooking((int) $payment->booking_id);
        });

        return back()->with('success', 'Pembayaran berhasil dikonfirmasi.');
    }

    /**
     * Proses refund — update status di sistem.
     * Integrasi Midtrans refund API bisa ditambahkan di sini nanti.
     */
    public function refund(Request $request, int $id)
    {
        $request->validate([
            'refund_reason' => ['required', 'string', 'max:500'],
        ]);

        $payment = DB::table('payments')->where('id', $id)->first();
        abort_if(! $payment, 404);
        abort_if($payment->status !== 'paid', 422, 'Hanya pembayaran yang sudah lunas yang bisa direfund.');

        DB::transaction(function () use ($payment) {
            DB::table('payments')->where('id', $payment->id)->update([
                'status'     => 'refunded',
                'updated_at' => now(),
            ]);

            DB::table('bookings')->where('id', $payment->booking_id)->update([
                'status'     => 'refunded',
                'updated_at' => now(),
            ]);

            $this->bookingSlotService->syncBookedSlotsForBooking((int) $payment->booking_id);
        });

        return back()->with('success', 'Refund berhasil diproses.');
    }

    /**
     * Cek status transaksi langsung ke Midtrans.
     */
    public function checkGateway(int $id)
    {
        $payment = DB::table('payments')->where('id', $id)->first();
        abort_if(! $payment, 404);

        try {
            $result = $this->midtrans->checkStatus($payment->payment_code);
            return back()->with('success', 'Status gateway: ' . ($result->transaction_status ?? 'unknown'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal cek gateway: ' . $e->getMessage());
        }
    }
}
