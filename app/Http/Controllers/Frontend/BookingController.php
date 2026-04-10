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
use Illuminate\Support\Facades\Auth;
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
            'authUser' => $request->user(),
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
            'customer_phone' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/'],
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'total_guests' => ['required', 'integer', 'min:1'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'main_product_id' => ['required', 'integer', 'exists:products,id'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['customer_email'] = (string) $request->user()->email;

        $booking = $this->bookingService->createAuthenticatedBooking($data, $request->user());

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

                $this->storeMergedGatewayResponse($booking['payment_code'], [
                    'provider' => 'midtrans',
                    'snap_token' => $snapToken,
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

        session()->put('guest_pending_booking_token', $booking['public_token']);

        return response()->json([
            'message' => 'Booking berhasil dibuat dan sedang menunggu pembayaran.',
            'booking' => $booking,
            'payment_gateway' => $paymentGateway,
            'redirect_url' => route('ticket.booking.status', ['token' => $booking['public_token']]),
        ], 201);
    }

    public function status(string $token)
    {
        $booking = $this->findBookingByPublicToken($token);

        abort_if(! $booking, 404);

        $items = DB::table('booking_items as bi')
            ->leftJoin('products as p', 'p.id', '=', 'bi.product_id')
            ->leftJoin('reviews as r', function ($join) use ($booking) {
                $join->on('r.product_id', '=', 'bi.product_id')
                    ->where('r.booking_id', '=', $booking->id)
                    ->where('r.user_id', '=', $booking->user_id);
            })
            ->select(
                'bi.product_id',
                'bi.product_name_snapshot',
                'bi.quantity',
                'bi.unit_price',
                'bi.subtotal',
                'bi.is_addon',
                'p.slug as product_slug',
                'r.id as review_id',
                'r.rating as review_rating',
                'r.comment as review_comment',
                'r.is_published as review_is_published',
                'r.updated_at as review_updated_at'
            )
            ->where('bi.booking_id', $booking->id)
            ->orderBy('bi.is_addon')
            ->orderBy('bi.id')
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

        $reviewEligibility = $this->reviewEligibility($booking, $payment);
        $this->syncGuestPendingBookingSession($booking, $payment);

        return view('frontend.booking-status', [
            'booking' => $booking,
            'items' => $items,
            'payment' => $payment,
            'reviewEligibility' => $reviewEligibility,
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

    public function storeReview(Request $request, string $token)
    {
        $booking = $this->findBookingByPublicToken($token);
        abort_if(! $booking, 404);

        $payment = DB::table('payments as py')
            ->leftJoin('payment_methods as pm', 'pm.id', '=', 'py.payment_method_id')
            ->select('py.status', 'pm.provider as payment_provider')
            ->where('py.booking_id', $booking->id)
            ->latest('py.created_at')
            ->first();

        $reviewEligibility = $this->reviewEligibility($booking, $payment);

        if (! $reviewEligibility['allowed']) {
            return redirect()
                ->route('ticket.booking.status', ['token' => $token])
                ->with('review_error', $reviewEligibility['message']);
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $bookingItem = DB::table('booking_items as bi')
            ->leftJoin('products as p', 'p.id', '=', 'bi.product_id')
            ->select('bi.product_id', 'bi.product_name_snapshot', 'p.slug as product_slug')
            ->where('bi.booking_id', $booking->id)
            ->where('bi.product_id', (int) $data['product_id'])
            ->where('bi.is_addon', false)
            ->first();

        if (! $bookingItem) {
            return redirect()
                ->route('ticket.booking.status', ['token' => $token])
                ->with('review_error', 'Produk yang dipilih tidak tersedia untuk diulas pada booking ini.')
                ->withInput();
        }

        $comment = trim((string) ($data['comment'] ?? ''));
        $existingReview = DB::table('reviews')
            ->where('user_id', $booking->user_id)
            ->where('booking_id', $booking->id)
            ->where('product_id', (int) $data['product_id'])
            ->first();

        if ($existingReview) {
            DB::table('reviews')
                ->where('id', $existingReview->id)
                ->update([
                    'rating' => (int) $data['rating'],
                    'comment' => $comment !== '' ? $comment : null,
                    'is_published' => true,
                    'updated_at' => now(),
                ]);

            $message = 'Ulasan berhasil diperbarui.';
        } else {
            DB::table('reviews')->insert([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'product_id' => (int) $data['product_id'],
                'rating' => (int) $data['rating'],
                'comment' => $comment !== '' ? $comment : null,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $message = 'Ulasan berhasil dikirim.';
        }

        $this->recalcProductRating((int) $data['product_id']);

        return redirect()
            ->route('ticket.booking.status', ['token' => $token])
            ->with('review_success', $message . ' Terima kasih sudah berbagi pengalaman untuk ' . $bookingItem->product_name_snapshot . '.');
    }

    public function resumePayment(string $token)
    {
        $payment = $this->bookingService->getPendingPaymentSummaryByToken($token);

        abort_if(! $payment, 404);
        abort_if(($payment['user_id'] ?? null) !== Auth::id(), 403);
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

        $decodedResponse = $this->decodeGatewayResponse($existingResponse);
        $snapToken = $decodedResponse['snap_token'] ?? null;

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

                $this->storeMergedGatewayResponse($payment['payment_code'], [
                    'provider' => 'midtrans',
                    'snap_token' => $snapToken,
                ]);
            } catch (\Throwable $exception) {
                $message = $exception->getMessage();

                if (str_contains(strtolower($message), 'order_id') && str_contains(strtolower($message), 'digunakan')) {
                    try {
                        $result = $this->midtransService->checkStatus($payment['payment_code']);
                        $paymentStatus = $this->midtransService->resolvePaymentStatus($result);

                        DB::table('payments')
                            ->where('payment_code', $payment['payment_code'])
                            ->update([
                                'status' => $paymentStatus,
                                'gateway_transaction_id' => $result->transaction_id ?? null,
                                'va_number' => $this->extractMidtransVaNumber($result),
                                'qr_url' => $this->extractMidtransQrUrl($result),
                                'paid_at' => $paymentStatus === 'paid' ? now() : null,
                                'updated_at' => now(),
                            ]);

                        $this->storeMergedGatewayResponse($payment['payment_code'], [
                            'provider' => 'midtrans',
                            'transaction_status' => $result->transaction_status ?? null,
                            'transaction_id' => $result->transaction_id ?? null,
                            'payment_type' => $result->payment_type ?? null,
                            'va_numbers' => isset($result->va_numbers) ? json_decode(json_encode($result->va_numbers), true) : null,
                            'actions' => isset($result->actions) ? json_decode(json_encode($result->actions), true) : null,
                            'raw_result' => json_decode(json_encode($result), true),
                        ]);

                        if ($paymentStatus === 'paid') {
                            DB::table('bookings')
                                ->where('booking_code', $payment['payment_code'])
                                ->where('status', '!=', 'cancelled')
                                ->update(['status' => 'confirmed', 'updated_at' => now()]);
                        }
                    } catch (\Throwable) {
                        // Cukup tampilkan pesan fallback di bawah.
                    }

                    return response()->json([
                        'message' => 'Transaksi Midtrans untuk booking ini sudah pernah dibuat. Gunakan detail pembayaran yang tampil di halaman ini atau klik cek status pembayaran.',
                        'payment_gateway' => [
                            'provider' => 'midtrans',
                            'mode' => 'snap',
                            'snap_token' => null,
                            'client_key' => $this->midtransClientKey(),
                        ],
                        'redirect_url' => route('ticket.booking.status', ['token' => $payment['public_token']]),
                    ]);
                }

                return response()->json([
                    'message' => 'Snap token Midtrans belum bisa disiapkan. ' . $message,
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
                    'paid_at' => $paymentStatus === 'paid' ? now() : null,
                    'updated_at' => now(),
                ]);

            $this->storeMergedGatewayResponse($payment->payment_code, [
                'provider' => 'midtrans',
                'transaction_status' => $result->transaction_status ?? null,
                'transaction_id' => $result->transaction_id ?? null,
                'payment_type' => $result->payment_type ?? null,
                'va_numbers' => isset($result->va_numbers) ? json_decode(json_encode($result->va_numbers), true) : null,
                'actions' => isset($result->actions) ? json_decode(json_encode($result->actions), true) : null,
                'raw_result' => json_decode(json_encode($result), true),
            ]);

            if ($paymentStatus === 'paid') {
                DB::table('bookings')
                    ->where('booking_code', $payment->payment_code)
                    ->update(['status' => 'confirmed', 'updated_at' => now()]);
            }

            $this->syncGuestPendingBookingSessionByToken($token, $paymentStatus);

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

    private function decodeGatewayResponse(mixed $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (is_object($payload)) {
            return json_decode(json_encode($payload), true) ?: [];
        }

        if (! is_string($payload) || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function storeMergedGatewayResponse(string $paymentCode, array $payload): void
    {
        $existing = DB::table('payments')
            ->where('payment_code', $paymentCode)
            ->value('gateway_response');

        $merged = array_replace_recursive(
            $this->decodeGatewayResponse($existing),
            array_filter($payload, fn ($value) => $value !== null)
        );

        DB::table('payments')
            ->where('payment_code', $paymentCode)
            ->update([
                'gateway_response' => json_encode($merged),
                'updated_at' => now(),
            ]);
    }

    private function findBookingByPublicToken(string $token): ?object
    {
        return DB::table('bookings as b')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->select(
                'b.id',
                'b.user_id',
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
            ->where('b.user_id', Auth::id())
            ->first();
    }

    private function reviewEligibility(object $booking, ?object $payment): array
    {
        if (! in_array($booking->status, ['confirmed', 'checked_in', 'completed'], true)) {
            return [
                'allowed' => false,
                'message' => 'Ulasan dapat dikirim setelah booking dikonfirmasi atau kunjungan selesai.',
            ];
        }

        if (Carbon::parse($booking->visit_date)->isFuture()) {
            return [
                'allowed' => false,
                'message' => 'Ulasan baru bisa dikirim setelah tanggal kunjungan berlangsung.',
            ];
        }

        if ($payment && ($payment->payment_provider ?? null) === 'midtrans' && ($payment->status ?? null) !== 'paid') {
            return [
                'allowed' => false,
                'message' => 'Ulasan baru tersedia setelah pembayaran terkonfirmasi.',
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Bagikan pengalamanmu setelah kunjungan selesai agar calon tamu lain mendapat gambaran yang lebih jelas.',
        ];
    }

    private function recalcProductRating(int $productId): void
    {
        $stats = DB::table('reviews')
            ->where('product_id', $productId)
            ->where('is_published', true)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')
            ->first();

        DB::table('products')
            ->where('id', $productId)
            ->update([
                'rating_avg' => round($stats->avg_rating ?? 0, 2),
                'review_count' => $stats->total ?? 0,
                'updated_at' => now(),
            ]);
    }

    private function syncGuestPendingBookingSession(object $booking, ?object $payment): void
    {
        $isPendingBooking = in_array($booking->status, ['pending', 'waiting_payment'], true);
        $isPendingPayment = ($payment?->status ?? null) === 'pending';

        if ($isPendingBooking && $isPendingPayment) {
            session()->put('guest_pending_booking_token', $booking->public_token);

            return;
        }

        if (session('guest_pending_booking_token') === $booking->public_token) {
            session()->forget('guest_pending_booking_token');
        }
    }

    private function syncGuestPendingBookingSessionByToken(string $publicToken, string $paymentStatus): void
    {
        if ($paymentStatus === 'pending') {
            session()->put('guest_pending_booking_token', $publicToken);

            return;
        }

        if (session('guest_pending_booking_token') === $publicToken) {
            session()->forget('guest_pending_booking_token');
        }
    }
}
