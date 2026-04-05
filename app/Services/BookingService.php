<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(private BookingSlotService $bookingSlotService) {}

    public function checkAvailability(int $productId, string $visitDate, int $totalGuests): array
    {
        $product = $this->getProduct($productId);
        $requiredUnits = $this->calculateQuantity($product, $totalGuests);
        $slot = $this->findSlot($productId, $visitDate);

        $issues = [];

        if (! $product->is_active) {
            $issues[] = 'Produk sedang tidak aktif.';
        }

        if ($totalGuests < $product->min_pax) {
            $issues[] = 'Jumlah tamu di bawah minimum pax produk ini.';
        }

        if ($totalGuests > $product->max_pax) {
            $issues[] = 'Jumlah tamu melebihi batas maksimum pax produk ini.';
        }

        if ($slot) {
            if ($slot->is_blocked) {
                $issues[] = 'Slot pada tanggal tersebut sedang diblokir.';
            }

            $remaining = max(0, (int) $slot->total_slots - (int) $slot->booked_slots);
            if ($remaining < $requiredUnits) {
                $issues[] = 'Kapasitas slot tidak mencukupi.';
            }
        } elseif ((int) $product->max_capacity < $requiredUnits) {
            $issues[] = 'Kapasitas produk tidak mencukupi untuk permintaan ini.';
        }

        return [
            'available' => empty($issues),
            'message' => empty($issues)
                ? ($slot ? 'Produk tersedia pada tanggal yang dipilih.' : 'Produk tersedia, slot spesifik belum diatur.')
                : implode(' ', $issues),
            'required_units' => $requiredUnits,
            'slot' => $slot ? [
                'id' => $slot->id,
                'slot_date' => $slot->slot_date,
                'start_time' => $slot->start_time,
                'remaining_capacity' => max(0, (int) $slot->total_slots - (int) $slot->booked_slots),
            ] : null,
        ];
    }

    public function createPublicBooking(array $payload): array
    {
        $quote = $this->buildPublicBookingQuote($payload);
        $paymentMethod = $quote['payment_method'];
        $items = $quote['items'];
        $subtotal = $quote['subtotal'];
        $feeAmount = $quote['fee_amount'];
        $totalAmount = $quote['total_amount'];
        $visitDate = $quote['visit_date'];
        $totalGuests = $quote['total_guests'];

        return DB::transaction(function () use ($payload, $items, $subtotal, $feeAmount, $totalAmount, $paymentMethod, $visitDate, $totalGuests) {
            $user = DB::table('users')->where('email', $payload['customer_email'])->first();

            if (! $user) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $payload['customer_name'],
                    'email' => $payload['customer_email'],
                    'password' => bcrypt(Str::random(20)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $userId = $user->id;
                DB::table('users')->where('id', $userId)->update([
                    'name' => $payload['customer_name'],
                    'updated_at' => now(),
                ]);
            }

            $bookingCode = $this->generateBookingCode();
            $publicToken = $this->generatePublicToken();

            $bookingId = DB::table('bookings')->insertGetId([
                'booking_code' => $bookingCode,
                'public_token' => $publicToken,
                'user_id' => $userId,
                'visit_date' => $visitDate,
                'checkout_date' => null,
                'total_guests' => $totalGuests,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'service_fee' => $feeAmount,
                'total_amount' => $totalAmount,
                'promo_code' => null,
                'status' => 'waiting_payment',
                'notes' => $payload['notes'] ?? null,
                'source' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($items as $index => $item) {
                $items[$index]['booking_id'] = $bookingId;
                $items[$index]['created_at'] = now();
                $items[$index]['updated_at'] = now();
            }

            DB::table('booking_items')->insert($items);

            $this->bookingSlotService->syncBookedSlotsForBooking($bookingId);

            DB::table('payments')->insert([
                'booking_id' => $bookingId,
                'payment_code' => $bookingCode,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $totalAmount,
                'fee_amount' => $feeAmount,
                'status' => 'pending',
                'gateway_transaction_id' => null,
                'gateway_order_id' => $bookingCode,
                'va_number' => null,
                'qr_url' => null,
                'gateway_response' => null,
                'paid_at' => null,
                'expired_at' => now()->addHours(24),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'booking_id' => $bookingId,
                'booking_code' => $bookingCode,
                'public_token' => $publicToken,
                'payment_code' => $bookingCode,
                'payment_method_id' => $paymentMethod->id,
                'payment_method' => $paymentMethod->name,
                'payment_type' => $paymentMethod->type,
                'payment_provider' => $paymentMethod->provider,
                'subtotal' => $subtotal,
                'fee_amount' => $feeAmount,
                'total_amount' => $totalAmount,
                'status' => 'waiting_payment',
                'customer_name' => $payload['customer_name'],
                'customer_email' => $payload['customer_email'],
                'customer_phone' => $payload['customer_phone'],
                'items' => collect($items)->map(fn ($item) => [
                    'product_id' => $item['product_id'],
                    'name' => $item['product_name_snapshot'],
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['unit_price'],
                ])->values()->all(),
            ];
        });
    }

    public function estimatePublicBooking(array $payload): array
    {
        $quote = $this->buildPublicBookingQuote($payload);

        return [
            'visit_date' => $quote['visit_date'],
            'total_guests' => $quote['total_guests'],
            'payment_method' => [
                'id' => $quote['payment_method']->id,
                'name' => $quote['payment_method']->name,
                'provider' => $quote['payment_method']->provider,
                'type' => $quote['payment_method']->type,
            ],
            'subtotal' => $quote['subtotal'],
            'fee_amount' => $quote['fee_amount'],
            'total_amount' => $quote['total_amount'],
            'items' => collect($quote['items'])->map(fn ($item) => [
                'product_id' => $item['product_id'],
                'name' => $item['product_name_snapshot'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
                'subtotal' => (float) $item['subtotal'],
                'is_addon' => (bool) $item['is_addon'],
            ])->values()->all(),
        ];
    }

    public function getPendingPaymentSummaryByToken(string $publicToken): ?array
    {
        $booking = DB::table('bookings as b')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->join('payments as py', 'py.booking_id', '=', 'b.id')
            ->leftJoin('payment_methods as pm', 'pm.id', '=', 'py.payment_method_id')
            ->select(
                'b.id as booking_id',
                'b.booking_code',
                'b.public_token',
                'b.total_amount',
                'b.status as booking_status',
                'u.name as customer_name',
                'u.email as customer_email',
                'py.payment_code',
                'py.status as payment_status',
                'pm.name as payment_method_name',
                'pm.provider as payment_provider',
                'pm.type as payment_type'
            )
            ->where('b.public_token', $publicToken)
            ->latest('py.created_at')
            ->first();

        if (! $booking) {
            return null;
        }

        $items = DB::table('booking_items')
            ->select('product_id', 'product_name_snapshot', 'quantity', 'unit_price')
            ->where('booking_id', $booking->booking_id)
            ->orderBy('id')
            ->get()
            ->map(fn ($item) => [
                'id' => (string) $item->product_id,
                'price' => (int) round((float) $item->unit_price),
                'quantity' => (int) $item->quantity,
                'name' => $item->product_name_snapshot,
            ])
            ->values()
            ->all();

        return [
            'booking_id' => (int) $booking->booking_id,
            'booking_code' => $booking->booking_code,
            'public_token' => $booking->public_token,
            'total_amount' => (float) $booking->total_amount,
            'booking_status' => $booking->booking_status,
            'customer_name' => $booking->customer_name,
            'customer_email' => $booking->customer_email,
            'payment_code' => $booking->payment_code,
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method_name,
            'payment_provider' => $booking->payment_provider,
            'payment_type' => $booking->payment_type,
            'items' => $items,
        ];
    }

    public function prepareAdminBookingItems(array $items, string $visitDate, int $totalGuests): array
    {
        $preparedItems = [];
        $subtotal = 0;

        foreach ($items as $index => $item) {
            $product = $this->getProduct((int) $item['product_id']);
            $quantity = (int) $item['quantity'];

            if (! $product->is_active) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => 'Produk sedang tidak aktif.',
                ]);
            }

            if ($totalGuests < (int) $product->min_pax) {
                throw ValidationException::withMessages([
                    'total_guests' => "Jumlah tamu belum memenuhi minimum pax untuk {$product->name}.",
                ]);
            }

            if ($totalGuests > (int) $product->max_pax) {
                throw ValidationException::withMessages([
                    'total_guests' => "Jumlah tamu melebihi batas maksimum pax untuk {$product->name}.",
                ]);
            }

            $availability = $this->checkAvailabilityForUnits($product->id, $visitDate, $quantity);
            if (! $availability['available']) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => $availability['message'],
                ]);
            }

            $lineTotal = (float) $product->price * $quantity;
            $subtotal += $lineTotal;

            $preparedItems[] = [
                'product_id' => $product->id,
                'slot_id' => $availability['slot']['id'] ?? null,
                'product_name_snapshot' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'subtotal' => $lineTotal,
                'is_addon' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return [
            'items' => $preparedItems,
            'subtotal' => $subtotal,
        ];
    }

    private function getProduct(int $productId): object
    {
        $product = DB::table('products as p')
            ->join('product_categories as pc', 'pc.id', '=', 'p.category_id')
            ->select(
                'p.id',
                'p.name',
                'p.slug',
                'p.price',
                'p.price_label',
                'p.min_pax',
                'p.max_pax',
                'p.max_capacity',
                'p.is_active',
                'pc.type as category_type'
            )
            ->where('p.id', $productId)
            ->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'product' => 'Produk tidak ditemukan.',
            ]);
        }

        return $product;
    }

    private function getPaymentMethod(int $paymentMethodId): object
    {
        $paymentMethod = DB::table('payment_methods')
            ->where('id', $paymentMethodId)
            ->where('is_active', true)
            ->first();

        if (! $paymentMethod) {
            throw ValidationException::withMessages([
                'payment_method_id' => 'Metode pembayaran tidak ditemukan atau sedang tidak aktif.',
            ]);
        }

        return $paymentMethod;
    }

    private function findSlot(int $productId, string $visitDate): ?object
    {
        return DB::table('product_slots')
            ->where('product_id', $productId)
            ->where('slot_date', $visitDate)
            ->orderBy('start_time')
            ->first();
    }

    private function calculateQuantity(object $product, int $totalGuests): int
    {
        return $product->price_label === '/orang' ? max(1, $totalGuests) : 1;
    }

    private function checkAvailabilityForUnits(int $productId, string $visitDate, int $requiredUnits): array
    {
        $product = $this->getProduct($productId);
        $slot = $this->findSlot($productId, $visitDate);

        $issues = [];

        if (! $product->is_active) {
            $issues[] = 'Produk sedang tidak aktif.';
        }

        if ($slot) {
            if ($slot->is_blocked) {
                $issues[] = 'Slot pada tanggal tersebut sedang diblokir.';
            }

            $remaining = max(0, (int) $slot->total_slots - (int) $slot->booked_slots);
            if ($remaining < $requiredUnits) {
                $issues[] = 'Kapasitas slot tidak mencukupi.';
            }
        } elseif ((int) $product->max_capacity < $requiredUnits) {
            $issues[] = 'Kapasitas produk tidak mencukupi untuk permintaan ini.';
        }

        return [
            'available' => empty($issues),
            'message' => empty($issues)
                ? ($slot ? 'Produk tersedia pada tanggal yang dipilih.' : 'Produk tersedia, slot spesifik belum diatur.')
                : implode(' ', $issues),
            'required_units' => $requiredUnits,
            'slot' => $slot ? [
                'id' => $slot->id,
                'slot_date' => $slot->slot_date,
                'start_time' => $slot->start_time,
                'remaining_capacity' => max(0, (int) $slot->total_slots - (int) $slot->booked_slots),
            ] : null,
        ];
    }

    private function calculatePaymentFee(object $paymentMethod, float $subtotal): float
    {
        $flat = (float) $paymentMethod->fee_flat;
        $percent = (float) $paymentMethod->fee_percent;

        return round($flat + (($subtotal * $percent) / 100), 2);
    }

    private function generateBookingCode(): string
    {
        $date = now()->format('Ymd');
        $prefix = "CAP-{$date}-";

        $last = DB::table('bookings')
            ->where('booking_code', 'like', "{$prefix}%")
            ->orderByDesc('booking_code')
            ->value('booking_code');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function generatePublicToken(): string
    {
        do {
            $token = Str::random(40);
        } while (DB::table('bookings')->where('public_token', $token)->exists());

        return $token;
    }

    private function buildPublicBookingQuote(array $payload): array
    {
        $mainProduct = $this->getProduct((int) $payload['main_product_id']);
        $addonProducts = collect($payload['addon_ids'] ?? [])
            ->unique()
            ->map(fn ($id) => $this->getProduct((int) $id))
            ->values();

        if (($mainProduct->category_type ?? null) !== 'internal') {
            throw ValidationException::withMessages([
                'main_product_id' => 'Produk utama harus berasal dari kategori internal.',
            ]);
        }

        foreach ($addonProducts as $addonProduct) {
            if (($addonProduct->category_type ?? null) !== 'addon') {
                throw ValidationException::withMessages([
                    'addon_ids' => 'Semua add-on harus berasal dari kategori add-on.',
                ]);
            }
        }

        $paymentMethod = $this->getPaymentMethod((int) $payload['payment_method_id']);
        $visitDate = $payload['visit_date'];
        $totalGuests = (int) $payload['total_guests'];

        $mainAvailability = $this->checkAvailability($mainProduct->id, $visitDate, $totalGuests);
        if (! $mainAvailability['available']) {
            throw ValidationException::withMessages([
                'main_product_id' => $mainAvailability['message'],
            ]);
        }

        $items = [];
        $subtotal = 0;

        $mainQuantity = $this->calculateQuantity($mainProduct, $totalGuests);
        $mainSubtotal = $mainQuantity * (float) $mainProduct->price;
        $subtotal += $mainSubtotal;
        $items[] = [
            'product_id' => $mainProduct->id,
            'slot_id' => $mainAvailability['slot']['id'] ?? null,
            'is_addon' => false,
            'product_name_snapshot' => $mainProduct->name,
            'quantity' => $mainQuantity,
            'unit_price' => $mainProduct->price,
            'subtotal' => $mainSubtotal,
        ];

        foreach ($addonProducts as $addonProduct) {
            $availability = $this->checkAvailability($addonProduct->id, $visitDate, $totalGuests);
            if (! $availability['available']) {
                throw ValidationException::withMessages([
                    'addon_ids' => 'Add-on "' . $addonProduct->name . '" tidak tersedia. ' . $availability['message'],
                ]);
            }

            $addonQuantity = $this->calculateQuantity($addonProduct, $totalGuests);
            $addonSubtotal = $addonQuantity * (float) $addonProduct->price;
            $subtotal += $addonSubtotal;
            $items[] = [
                'product_id' => $addonProduct->id,
                'slot_id' => $availability['slot']['id'] ?? null,
                'is_addon' => true,
                'product_name_snapshot' => $addonProduct->name,
                'quantity' => $addonQuantity,
                'unit_price' => $addonProduct->price,
                'subtotal' => $addonSubtotal,
            ];
        }

        $feeAmount = $this->calculatePaymentFee($paymentMethod, $subtotal);
        $totalAmount = $subtotal + $feeAmount;

        if ($totalAmount < (float) $paymentMethod->min_amount) {
            throw ValidationException::withMessages([
                'payment_method_id' => 'Total transaksi belum memenuhi minimum pembayaran untuk metode ini.',
            ]);
        }

        if ($paymentMethod->max_amount !== null && $totalAmount > (float) $paymentMethod->max_amount) {
            throw ValidationException::withMessages([
                'payment_method_id' => 'Total transaksi melebihi batas maksimum metode pembayaran ini.',
            ]);
        }

        return [
            'main_product' => $mainProduct,
            'addon_products' => $addonProducts,
            'payment_method' => $paymentMethod,
            'visit_date' => $visitDate,
            'total_guests' => $totalGuests,
            'items' => $items,
            'subtotal' => $subtotal,
            'fee_amount' => $feeAmount,
            'total_amount' => $totalAmount,
        ];
    }
}
