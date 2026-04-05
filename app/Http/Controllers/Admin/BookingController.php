<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Services\BookingSlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function __construct(
        private BookingSlotService $bookingSlotService,
        private BookingService $bookingService,
    ) {}

    private array $statusFlow = [
        'pending'         => ['waiting_payment', 'confirmed', 'cancelled'],
        'waiting_payment' => ['confirmed', 'cancelled'],
        'confirmed'       => ['checked_in', 'cancelled'],
        'checked_in'      => ['completed'],
        'completed'       => [],
        'cancelled'       => ['refunded'],
        'refunded'        => [],
    ];

    private function mitraId(): ?int
    {
        $user = auth()->user();
        return $user->hasRole('Super Admin') ? null : $user->mitraProfile?->id;
    }

    public function index(Request $request)
    {
        $mitraId = $this->mitraId();

        $query = DB::table('bookings as b')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->select('b.*', 'u.name as user_name', 'u.email as user_email')
            ->orderByDesc('b.created_at');

        // Mitra hanya lihat booking yang mengandung produknya
        if ($mitraId) {
            $query->whereExists(function ($sub) use ($mitraId) {
                $sub->select(DB::raw(1))
                    ->from('booking_items as bi')
                    ->join('products as p', 'p.id', '=', 'bi.product_id')
                    ->whereColumn('bi.booking_id', 'b.id')
                    ->where('p.mitra_id', $mitraId);
            });
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($w) => $w
                ->where('b.booking_code', 'like', "%$q%")
                ->orWhere('u.name', 'like', "%$q%")
                ->orWhere('u.email', 'like', "%$q%")
            );
        }

        if ($request->filled('status')) {
            $query->where('b.status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('b.visit_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('b.visit_date', '<=', $request->date_to);
        }

        if ($request->filled('source')) {
            $query->where('b.source', $request->source);
        }

        $bookings = $query->paginate(15)->withQueryString();

        $statusCountsQuery = DB::table('bookings');
        if ($mitraId) {
            $statusCountsQuery->whereExists(function ($sub) use ($mitraId) {
                $sub->select(DB::raw(1))
                    ->from('booking_items as bi')
                    ->join('products as p', 'p.id', '=', 'bi.product_id')
                    ->whereColumn('bi.booking_id', 'bookings.id')
                    ->where('p.mitra_id', $mitraId);
            });
        }
        $statusCounts = $statusCountsQuery
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('backend.bookings.index', compact('bookings', 'statusCounts'));
    }

    public function create()
    {
        $mitraId  = $this->mitraId();
        $products = DB::table('products')
            ->where('is_active', true)
            ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId))
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'price_label', 'min_pax', 'max_pax', 'category_id']);

        $paymentMethods = DB::table('payment_methods')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code', 'type']);

        return view('backend.bookings.form', [
            'booking'        => null,
            'items'          => [],
            'products'       => $products,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'      => ['required', 'string', 'max:150'],
            'customer_email'     => ['required', 'email', 'max:150'],
            'visit_date'         => ['required', 'date', 'after_or_equal:today'],
            'checkout_date'      => ['nullable', 'date', 'after_or_equal:visit_date'],
            'total_guests'       => ['required', 'integer', 'min:1'],
            'source'             => ['required', 'in:web,mobile,whatsapp,walk_in'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'promo_code'         => ['nullable', 'string', 'max:50'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $user = DB::table('users')->where('email', $data['customer_email'])->first();
        if (! $user) {
            $userId = DB::table('users')->insertGetId([
                'name'       => $data['customer_name'],
                'email'      => $data['customer_email'],
                'password'   => bcrypt(Str::random(16)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $userId = $user->id;
        }

<<<<<<< HEAD
        $preparedBooking = $this->bookingService->prepareAdminBookingItems(
            $data['items'],
            $data['visit_date'],
            (int) $data['total_guests'],
        );
        $subtotal = $preparedBooking['subtotal'];
        $itemRows = $preparedBooking['items'];
=======
        $subtotal = 0;
        $itemRows = [];
        foreach ($data['items'] as $item) {
            $product   = DB::table('products')->find($item['product_id']);
            $lineTotal = $product->price * $item['quantity'];
            $subtotal += $lineTotal;
            $itemRows[] = [
                'product_id'            => $product->id,
                'product_name_snapshot' => $product->name,
                'quantity'              => $item['quantity'],
                'unit_price'            => $product->price,
                'subtotal'              => $lineTotal,
                'is_addon'              => false,
                'created_at'            => now(),
                'updated_at'            => now(),
            ];
        }
>>>>>>> 92fdba5 (perbaikan bahasa , role akses , dan minor)

        $bookingCode = $this->generateBookingCode();
        $bookingId   = DB::table('bookings')->insertGetId([
            'booking_code'    => $bookingCode,
            'public_token'    => $this->bookingService->generatePublicToken(),
            'user_id'         => $userId,
            'visit_date'      => $data['visit_date'],
            'checkout_date'   => $data['checkout_date'] ?? null,
            'total_guests'    => $data['total_guests'],
            'subtotal'        => $subtotal,
            'discount_amount' => 0,
            'service_fee'     => 0,
            'total_amount'    => $subtotal,
            'promo_code'      => $data['promo_code'] ?? null,
            'status'          => 'pending',
            'notes'           => $data['notes'] ?? null,
            'source'          => $data['source'],
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        foreach ($itemRows as &$row) {
            $row['booking_id'] = $bookingId;
        }
        DB::table('booking_items')->insert($itemRows);
        $this->bookingSlotService->syncBookedSlotsForBooking($bookingId);

        return redirect()->route('admin.bookings.show', $bookingId)
            ->with('success', "Booking {$bookingCode} berhasil dibuat.");
    }

    public function show(int $id)
    {
        $mitraId = $this->mitraId();

        $query = DB::table('bookings as b')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->select('b.*', 'u.name as user_name', 'u.email as user_email')
            ->where('b.id', $id);

        if ($mitraId) {
            $query->whereExists(function ($sub) use ($mitraId) {
                $sub->select(DB::raw(1))
                    ->from('booking_items as bi')
                    ->join('products as p', 'p.id', '=', 'bi.product_id')
                    ->whereColumn('bi.booking_id', 'b.id')
                    ->where('p.mitra_id', $mitraId);
            });
        }

        $booking = $query->first();
        abort_if(! $booking, 404);

        $items = DB::table('booking_items as bi')
            ->join('products as p', 'p.id', '=', 'bi.product_id')
            ->select('bi.*', 'p.slug as product_slug')
            ->where('bi.booking_id', $id)
            ->get();

        $payment = DB::table('payments as py')
            ->leftJoin('payment_methods as pm', 'pm.id', '=', 'py.payment_method_id')
            ->select('py.*', 'pm.name as method_name', 'pm.type as method_type')
            ->where('py.booking_id', $id)
            ->latest('py.created_at')
            ->first();

        $allowedTransitions = $this->statusFlow[$booking->status] ?? [];

        return view('backend.bookings.detail', compact('booking', 'items', 'payment', 'allowedTransitions'));
    }

    public function edit(int $id)
    {
        $mitraId = $this->mitraId();
        $booking = DB::table('bookings as b')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->select('b.*', 'u.name as user_name', 'u.email as user_email')
            ->where('b.id', $id)
            ->first();

        abort_if(! $booking, 404);
        abort_if(in_array($booking->status, ['completed', 'refunded']), 403, 'Booking ini tidak bisa diedit.');

        $items    = DB::table('booking_items')->where('booking_id', $id)->get();
        $products = DB::table('products')
            ->where('is_active', true)
            ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId))
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'price_label', 'min_pax', 'max_pax', 'category_id']);

        $paymentMethods = DB::table('payment_methods')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code', 'type']);

        return view('backend.bookings.form', compact('booking', 'items', 'products', 'paymentMethods'));
    }

    public function update(Request $request, int $id)
    {
        $booking = DB::table('bookings')->where('id', $id)->first();
        abort_if(! $booking, 404);

        $data = $request->validate([
            'visit_date'    => ['required', 'date'],
            'checkout_date' => ['nullable', 'date', 'after_or_equal:visit_date'],
            'total_guests'  => ['required', 'integer', 'min:1'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        DB::table('bookings')->where('id', $id)->update([
            'visit_date'    => $data['visit_date'],
            'checkout_date' => $data['checkout_date'] ?? null,
            'total_guests'  => $data['total_guests'],
            'notes'         => $data['notes'] ?? null,
            'updated_at'    => now(),
        ]);

        return redirect()->route('admin.bookings.show', $id)
            ->with('success', 'Booking berhasil diperbarui.');
    }

    public function updateStatus(Request $request, int $id)
    {
        $booking = DB::table('bookings')->where('id', $id)->first();
        abort_if(! $booking, 404);

        $allowed = $this->statusFlow[$booking->status] ?? [];
        $request->validate([
            'status' => ['required', 'in:' . implode(',', $allowed)],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        DB::table('bookings')->where('id', $id)->update([
            'status'     => $request->status,
            'updated_at' => now(),
        ]);

        $this->bookingSlotService->syncBookedSlotsForBooking($id);

        return back()->with('success', 'Status booking berhasil diubah ke ' . $request->status . '.');
    }

    private function generateBookingCode(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "CAP-{$date}-";
        $last   = DB::table('bookings')
            ->where('booking_code', 'like', "{$prefix}%")
            ->orderByDesc('booking_code')
            ->value('booking_code');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
