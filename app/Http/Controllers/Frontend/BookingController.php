<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\BookingService;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private MidtransService $midtransService,
    ) {}

    private function midtransClientKey(): string
    {
        return (string) PaymentSetting::get('midtrans_client_key', config('midtrans.client_key', ''));
    }

    private function midtransServerKey(): string
    {
        return (string) PaymentSetting::get('midtrans_server_key', config('midtrans.server_key', ''));
    }

    public function index(Request $request)
    {
        $searchQuery = trim((string) $request->query('q', ''));
        $categoryQuery = trim((string) $request->query('category', ''));

        $mainCategories = ProductCategory::query()
            ->where('is_active', true)
            ->where('type', 'internal')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'label', 'color_hex']);

        $mainProducts = Product::query()
            ->with(['category', 'primaryImage'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('type', 'internal'))
            ->when($categoryQuery !== '' && $categoryQuery !== 'all', function ($query) use ($categoryQuery) {
                $query->whereHas('category', fn ($category) => $category->where('slug', $categoryQuery));
            })
            ->when($searchQuery !== '', function ($query) use ($searchQuery) {
                $query->where(function ($search) use ($searchQuery) {
                    $search
                        ->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('slug', 'like', "%{$searchQuery}%")
                        ->orWhere('short_desc', 'like', "%{$searchQuery}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($searchQuery) {
                            $categoryQuery
                                ->where('name', 'like', "%{$searchQuery}%")
                                ->orWhere('label', 'like', "%{$searchQuery}%");
                        });
                });
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $addonProducts = Product::query()
            ->with(['category', 'primaryImage'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('type', 'addon'))
            ->when($searchQuery !== '', function ($query) use ($searchQuery) {
                $query->where(function ($search) use ($searchQuery) {
                    $search
                        ->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('slug', 'like', "%{$searchQuery}%")
                        ->orWhere('short_desc', 'like', "%{$searchQuery}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($searchQuery) {
                            $categoryQuery
                                ->where('name', 'like', "%{$searchQuery}%")
                                ->orWhere('label', 'like', "%{$searchQuery}%");
                        });
                });
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $paymentMethods = DB::table('payment_methods')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'code',
                'provider',
                'type',
                'fee_flat',
                'fee_percent',
            ]);

        return view('frontend.booking', [
            'mainCategories' => $mainCategories,
            'mainProducts' => $mainProducts,
            'addonProducts' => $addonProducts,
            'paymentMethods' => $paymentMethods,
            'preselectedProductSlug' => (string) $request->query('product', ''),
            'preselectedCategorySlug' => $categoryQuery,
            'prefilledVisitDate' => (string) $request->query('date', ''),
            'prefilledGuests' => max(1, (int) $request->query('guests', 2)),
            'searchQuery' => $searchQuery,
            'midtransClientKey' => $this->midtransClientKey(),
        ]);
    }

    public function product(Request $request, string $slug)
    {
        $product = Product::query()
            ->with(['category', 'primaryImage'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('type', 'internal'))
            ->firstOrFail();

        return view('frontend.booking-product', [
            'product' => $product,
            'prefilledVisitDate' => (string) $request->query('date', now()->toDateString()),
            'prefilledGuests' => max(1, (int) $request->query('guests', 2)),
        ]);
    }

    public function productCalendar(Request $request, string $slug)
    {
        $product = Product::query()
            ->select(['id', 'max_capacity'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('type', 'internal'))
            ->firstOrFail();

        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $month = $validated['month'] ?? now()->format('Y-m');
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $today = now()->startOfDay();

        $slotRows = DB::table('product_slots')
            ->selectRaw('
                slot_date,
                COUNT(*) as slot_count,
                SUM(total_slots) as total_slots,
                SUM(booked_slots) as booked_slots,
                SUM(CASE WHEN is_blocked = 1 THEN 1 ELSE 0 END) as blocked_count
            ')
            ->where('product_id', $product->id)
            ->whereBetween('slot_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->groupBy('slot_date')
            ->orderBy('slot_date')
            ->get()
            ->keyBy('slot_date');

        $days = [];
        $cursor = $monthStart->copy();

        while ($cursor->lte($monthEnd)) {
            $date = $cursor->toDateString();
            $slot = $slotRows->get($date);

            if ($slot) {
                $remaining = max(0, (int) $slot->total_slots - (int) $slot->booked_slots);
                $allBlocked = (int) $slot->blocked_count >= (int) $slot->slot_count;

                $status = $allBlocked
                    ? 'blocked'
                    : ($remaining > 0 ? 'available' : 'full');

                $days[] = [
                    'date' => $date,
                    'day' => (int) $cursor->day,
                    'remaining_capacity' => $remaining,
                    'total_slots' => (int) $slot->total_slots,
                    'booked_slots' => (int) $slot->booked_slots,
                    'slot_count' => (int) $slot->slot_count,
                    'status' => $cursor->lt($today) ? 'past' : $status,
                    'is_past' => $cursor->lt($today),
                    'is_fallback' => false,
                ];

                $cursor->addDay();

                continue;
            }

            $days[] = [
                'date' => $date,
                'day' => (int) $cursor->day,
                'remaining_capacity' => (int) $product->max_capacity,
                'total_slots' => (int) $product->max_capacity,
                'booked_slots' => 0,
                'slot_count' => 0,
                'status' => $cursor->lt($today) ? 'past' : 'default',
                'is_past' => $cursor->lt($today),
                'is_fallback' => true,
            ];

            $cursor->addDay();
        }

        return response()->json([
            'month' => $monthStart->format('Y-m'),
            'label' => $monthStart->translatedFormat('F Y'),
            'starts_on' => $monthStart->toDateString(),
            'ends_on' => $monthEnd->toDateString(),
            'days' => $days,
        ]);
    }

    public function availability(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'total_guests' => ['required', 'integer', 'min:1'],
        ]);

        return response()->json(
            $this->bookingService->checkAvailability(
                (int) $data['product_id'],
                $data['visit_date'],
                (int) $data['total_guests'],
            )
        );
    }

    public function estimate(Request $request)
    {
        $data = $request->validate([
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'total_guests' => ['required', 'integer', 'min:1'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'main_product_id' => ['required', 'integer', 'exists:products,id'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ]);

        return response()->json([
            'message' => 'Estimasi harga berhasil dihitung dari backend.',
            'estimate' => $this->bookingService->estimatePublicBooking($data),
        ]);
    }

    public function checkout(Request $request)
    {
        $request->merge([
            'customer_name' => trim((string) $request->input('customer_name', '')),
            'customer_email' => strtolower(trim((string) $request->input('customer_email', ''))),
            'customer_phone' => preg_replace('/\s+/', '', (string) $request->input('customer_phone', '')),
            'notes' => trim((string) $request->input('notes', '')),
        ]);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'min:3', 'max:150'],
            'customer_email' => ['required', 'email:rfc', 'max:150'],
            'customer_phone' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[0-9]{7,13}$/'],
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'total_guests' => ['required', 'integer', 'min:1'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'main_product_id' => ['required', 'integer', 'exists:products,id'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = $this->bookingService->createPublicBooking($data);

        $paymentGateway = null;

        if (($booking['payment_provider'] ?? null) === 'midtrans') {
            try {
                if ($this->midtransServerKey() === '' || $this->midtransClientKey() === '') {
                    throw new \RuntimeException('Konfigurasi Midtrans belum lengkap.');
                }

                $snapToken = $this->midtransService->createTokenFromBooking(
                    $booking['payment_code'],
                    (int) round((float) $booking['total_amount']),
                    [
                        'first_name' => $booking['customer_name'],
                        'email' => $booking['customer_email'],
                        'phone' => $booking['customer_phone'],
                    ],
                    collect($booking['items'])->map(fn ($item) => [
                        'id' => (string) $item['product_id'],
                        'price' => (int) round((float) $item['price']),
                        'quantity' => (int) $item['quantity'],
                        'name' => $item['name'],
                    ])->values()->all(),
                );

                $paymentGateway = [
                    'provider' => 'midtrans',
                    'mode' => 'snap',
                    'snap_token' => $snapToken,
                    'client_key' => $this->midtransClientKey(),
                ];

                DB::table('payments')
                    ->where('payment_code', $booking['payment_code'])
                    ->update([
                        'gateway_response' => json_encode([
                            'provider' => 'midtrans',
                            'snap_token' => $snapToken,
                        ]),
                        'updated_at' => now(),
                    ]);
            } catch (\Throwable $exception) {
                $paymentGateway = [
                    'provider' => 'midtrans',
                    'mode' => 'snap',
                    'snap_token' => null,
                    'client_key' => $this->midtransClientKey(),
                    'error' => $exception->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Booking berhasil dibuat dan sedang menunggu pembayaran.',
            'booking' => $booking,
            'payment_gateway' => $paymentGateway,
            'redirect_url' => route('ticket.booking.status', ['token' => $booking['public_token']]),
        ], 201);
    }

    public function status(string $token)
    {
        $booking = DB::table('bookings as b')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->select(
                'b.id',
                'b.booking_code',
                'b.public_token',
                'b.visit_date',
                'b.checkout_date',
                'b.total_guests',
                'b.subtotal',
                'b.service_fee',
                'b.total_amount',
                'b.status',
                'b.notes',
                'b.created_at',
                'u.name as customer_name',
                'u.email as customer_email'
            )
            ->where('b.public_token', $token)
            ->first();

        abort_if(! $booking, 404);

        $items = DB::table('booking_items')
            ->select(
                'product_name_snapshot',
                'quantity',
                'unit_price',
                'subtotal',
                'is_addon'
            )
            ->where('booking_id', $booking->id)
            ->orderBy('is_addon')
            ->orderBy('id')
            ->get();

        $payment = DB::table('payments as py')
            ->leftJoin('payment_methods as pm', 'pm.id', '=', 'py.payment_method_id')
            ->select(
                'py.payment_code',
                'py.amount',
                'py.fee_amount',
                'py.status',
                'py.va_number',
                'py.qr_url',
                'py.gateway_transaction_id',
                'py.gateway_response',
                'py.paid_at',
                'py.expired_at',
                'pm.name as payment_method_name',
                'pm.provider as payment_provider',
                'pm.type as payment_type'
            )
            ->where('py.booking_id', $booking->id)
            ->latest('py.created_at')
            ->first();

        if ($payment) {
            $gatewayResponse = json_decode((string) ($payment->gateway_response ?? ''), true);

            if (! $payment->va_number) {
                $payment->va_number = $this->extractMidtransVaNumber($gatewayResponse);
            }

            if (! $payment->qr_url) {
                $payment->qr_url = $this->extractMidtransQrUrl($gatewayResponse);
            }
        }

        return view('frontend.booking-status', [
            'booking' => $booking,
            'items' => $items,
            'payment' => $payment,
            'midtransClientKey' => $this->midtransClientKey(),
            'statusLabels' => [
                'pending' => 'Pending',
                'waiting_payment' => 'Menunggu Pembayaran',
                'confirmed' => 'Terkonfirmasi',
                'checked_in' => 'Checked In',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                'refunded' => 'Refunded',
                'paid' => 'Lunas',
                'failed' => 'Gagal',
                'expired' => 'Kedaluwarsa',
            ],
        ]);
    }

    public function resumePayment(string $token)
    {
        $payment = $this->bookingService->getPendingPaymentSummaryByToken($token);

        abort_if(! $payment, 404);
        abort_if(($payment['payment_provider'] ?? null) !== 'midtrans', 422, 'Metode pembayaran ini tidak menggunakan Midtrans.');
        abort_if(($payment['payment_status'] ?? null) !== 'pending', 422, 'Pembayaran ini sudah tidak berada pada status pending.');
        abort_if(($payment['booking_status'] ?? null) === 'cancelled', 422, 'Booking sudah dibatalkan.');

        if ($this->midtransServerKey() === '' || $this->midtransClientKey() === '') {
            return response()->json([
                'message' => 'Konfigurasi Midtrans belum lengkap.',
            ], 422);
        }

        // Cek apakah snap token lama masih tersimpan di gateway_response
        $existingResponse = DB::table('payments')
            ->where('payment_code', $payment['payment_code'])
            ->value('gateway_response');

        $snapToken = null;
        if ($existingResponse) {
            $decoded = json_decode($existingResponse, true);
            $snapToken = $decoded['snap_token'] ?? null;
        }

        // Kalau tidak ada token lama, buat baru
        if (! $snapToken) {
            try {
                $snapToken = $this->midtransService->createTokenFromBooking(
                    $payment['payment_code'],
                    (int) round((float) $payment['total_amount']),
                    [
                        'first_name' => $payment['customer_name'],
                        'email' => $payment['customer_email'],
                    ],
                    $payment['items'],
                );

                DB::table('payments')
                    ->where('payment_code', $payment['payment_code'])
                    ->update([
                        'gateway_response' => json_encode([
                            'provider' => 'midtrans',
                            'snap_token' => $snapToken,
                        ]),
                        'updated_at' => now(),
                    ]);
            } catch (\Throwable $exception) {
                return response()->json([
                    'message' => 'Snap token Midtrans belum bisa disiapkan. ' . $exception->getMessage(),
                ], 422);
            }
        }

        return response()->json([
            'message' => 'Snap token Midtrans berhasil disiapkan.',
            'payment_gateway' => [
                'provider' => 'midtrans',
                'mode' => 'snap',
                'snap_token' => $snapToken,
                'client_key' => $this->midtransClientKey(),
            ],
            'redirect_url' => route('ticket.booking.status', ['token' => $payment['public_token']]),
        ]);
    }

    public function syncPaymentStatus(string $token)
    {
        $payment = DB::table('payments as py')
            ->join('bookings as b', 'b.id', '=', 'py.booking_id')
            ->select('py.payment_code', 'py.status as payment_status', 'b.status as booking_status')
            ->where('b.public_token', $token)
            ->latest('py.created_at')
            ->first();

        abort_if(! $payment, 404);

        if ($payment->payment_status === 'paid') {
            return response()->json(['status' => 'paid', 'message' => 'Pembayaran sudah terkonfirmasi.']);
        }

        try {
            $result = $this->midtransService->checkStatus($payment->payment_code);
            $transactionStatus = $result->transaction_status ?? null;
            $fraudStatus = $result->fraud_status ?? null;

            $paymentStatus = match (true) {
                $transactionStatus === 'capture' && $fraudStatus === 'accept' => 'paid',
                $transactionStatus === 'settlement' => 'paid',
                $transactionStatus === 'pending' => 'pending',
                $transactionStatus === 'deny' => 'failed',
                $transactionStatus === 'expire' => 'expired',
                $transactionStatus === 'cancel' => 'failed',
                default => 'pending',
            };

            DB::table('payments')
                ->where('payment_code', $payment->payment_code)
                ->update([
                    'status' => $paymentStatus,
                    'gateway_transaction_id' => $result->transaction_id ?? null,
                    'va_number' => $this->extractMidtransVaNumber($result),
                    'qr_url' => $this->extractMidtransQrUrl($result),
                    'gateway_response' => json_encode($result),
                    'paid_at' => $paymentStatus === 'paid' ? now() : null,
                    'updated_at' => now(),
                ]);

            if ($paymentStatus === 'paid') {
                DB::table('bookings')
                    ->where('booking_code', $payment->payment_code)
                    ->update(['status' => 'confirmed', 'updated_at' => now()]);
            }

            return response()->json(['status' => $paymentStatus]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function finish(Request $request)
    {
        $orderId = (string) $request->query('order_id', '');

        if ($orderId === '') {
            return redirect()->route('ticket.booking')
                ->with('error', 'Informasi booking tidak ditemukan setelah proses pembayaran.');
        }

        $publicToken = DB::table('bookings')
            ->where('booking_code', $orderId)
            ->value('public_token');

        if (! $publicToken) {
            return redirect()->route('ticket.booking')
                ->with('error', 'Status booking tidak ditemukan setelah proses pembayaran.');
        }

        return redirect()->route('ticket.booking.status', ['token' => $publicToken, 'auto_sync' => '1']);
    }

    private function extractMidtransVaNumber(object|array|null $payload): ?string
    {
        if (! $payload) {
            return null;
        }

        $data = is_array($payload) ? $payload : (array) $payload;

        if (! empty($data['va_number'])) {
            return (string) $data['va_number'];
        }

        if (! empty($data['permata_va_number'])) {
            return (string) $data['permata_va_number'];
        }

        if (! empty($data['bill_key'])) {
            return (string) $data['bill_key'];
        }

        $vaNumbers = $data['va_numbers'] ?? null;

        if (is_array($vaNumbers) && ! empty($vaNumbers[0])) {
            $first = is_array($vaNumbers[0]) ? $vaNumbers[0] : (array) $vaNumbers[0];

            return isset($first['va_number']) ? (string) $first['va_number'] : null;
        }

        return null;
    }

    private function extractMidtransQrUrl(object|array|null $payload): ?string
    {
        if (! $payload) {
            return null;
        }

        $data = is_array($payload) ? $payload : (array) $payload;

        return isset($data['qr_url']) ? (string) $data['qr_url'] : null;
    }
}
