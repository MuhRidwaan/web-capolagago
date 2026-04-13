<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isMitra = $user?->hasRole('Mitra') ?? false;
        $mitraId = $isMitra ? $user?->mitraProfile?->id : null;

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $productBase = DB::table('products as p');
        $this->applyProductScope($productBase, $isMitra, $mitraId, 'p');

        $bookingsForMonth = DB::table('bookings as b');
        $this->applyBookingScope($bookingsForMonth, $isMitra, $mitraId, 'b');

        $bookingStatusCounts = DB::table('bookings as b');
        $this->applyBookingScope($bookingStatusCounts, $isMitra, $mitraId, 'b');
        $bookingStatusCounts = $bookingStatusCounts
            ->selectRaw('b.status, COUNT(*) as total')
            ->groupBy('b.status')
            ->pluck('total', 'status');

        $sourceBreakdown = DB::table('bookings as b');
        $this->applyBookingScope($sourceBreakdown, $isMitra, $mitraId, 'b');
        $sourceBreakdown = $sourceBreakdown
            ->selectRaw('b.source, COUNT(*) as total')
            ->groupBy('b.source')
            ->orderByDesc('total')
            ->get();

        $topProducts = DB::table('booking_items as bi')
            ->join('products as p', 'p.id', '=', 'bi.product_id')
            ->join('bookings as b', 'b.id', '=', 'bi.booking_id')
            ->leftJoin('product_categories as pc', 'pc.id', '=', 'p.category_id')
            ->whereIn('b.status', ['confirmed', 'checked_in', 'completed'])
            ->whereBetween('b.visit_date', [$monthStart, $monthEnd]);
        $this->applyProductScope($topProducts, $isMitra, $mitraId, 'p');
        $topProducts = $topProducts
            ->selectRaw('
                p.name as product_name,
                COALESCE(pc.label, \'-\') as category_name,
                SUM(bi.quantity) as total_qty,
                SUM(bi.subtotal) as total_revenue
            ')
            ->groupBy('p.name', 'pc.label')
            ->orderByDesc('total_revenue')
            ->limit(6)
            ->get();

        $upcomingVisits = DB::table('bookings as b')
            ->join('users as u', 'u.id', '=', 'b.user_id');
        $this->applyBookingScope($upcomingVisits, $isMitra, $mitraId, 'b');
        $upcomingVisits = $upcomingVisits
            ->select('b.booking_code', 'u.name as customer_name', 'b.visit_date', 'b.status', 'b.total_guests')
            ->whereDate('b.visit_date', '>=', $today)
            ->whereIn('b.status', ['confirmed', 'checked_in', 'waiting_payment'])
            ->orderBy('b.visit_date')
            ->limit(6)
            ->get();

        if ($isMitra) {
            $salesBase = DB::table('booking_items as bi')
                ->join('products as p', 'p.id', '=', 'bi.product_id')
                ->join('bookings as b', 'b.id', '=', 'bi.booking_id')
                ->whereIn('b.status', ['confirmed', 'checked_in', 'completed'])
                ->whereBetween('b.visit_date', [$monthStart, $monthEnd]);
            $this->applyProductScope($salesBase, true, $mitraId, 'p');

            $revenueMonth = (clone $salesBase)->sum('bi.subtotal');
            $bookingsMonth = (clone $salesBase)
                ->selectRaw('COUNT(DISTINCT bi.booking_id) as total_bookings')
                ->value('total_bookings');
            $attentionCount = DB::table('bookings as b');
            $this->applyBookingScope($attentionCount, true, $mitraId, 'b');
            $attentionCount = $attentionCount->whereIn('b.status', ['pending', 'waiting_payment'])->count();

            $recentBookings = DB::table('bookings as b')
                ->join('users as u', 'u.id', '=', 'b.user_id')
                ->join('booking_items as bi', 'bi.booking_id', '=', 'b.id')
                ->join('products as p', 'p.id', '=', 'bi.product_id');
            $this->applyProductScope($recentBookings, true, $mitraId, 'p');
            $recentBookings = $recentBookings
                ->selectRaw('
                    b.booking_code,
                    u.name as customer_name,
                    b.visit_date,
                    b.status,
                    SUM(bi.subtotal) as total_amount,
                    COUNT(DISTINCT bi.id) as item_count
                ')
                ->groupBy('b.booking_code', 'u.name', 'b.visit_date', 'b.status', 'b.created_at')
                ->orderByDesc('b.created_at')
                ->limit(6)
                ->get();

            $recentActivity = DB::table('booking_items as bi')
                ->join('products as p', 'p.id', '=', 'bi.product_id')
                ->join('bookings as b', 'b.id', '=', 'bi.booking_id')
                ->join('users as u', 'u.id', '=', 'b.user_id')
                ->whereIn('b.status', ['confirmed', 'checked_in', 'completed']);
            $this->applyProductScope($recentActivity, true, $mitraId, 'p');
            $recentActivity = $recentActivity
                ->select('p.name as product_name', 'u.name as customer_name', 'b.booking_code', 'bi.quantity', 'bi.subtotal', 'b.visit_date')
                ->orderByDesc('b.created_at')
                ->limit(6)
                ->get();

            $hero = [
                'eyebrow' => 'Ringkasan Mitra',
                'title' => 'Pantau performa produk dan booking mitra dari satu layar.',
                'subtitle' => 'Dashboard ini menyorot penjualan bulan berjalan, antrean booking yang perlu diperhatikan, dan produk yang paling menghasilkan.',
            ];

            $stats = [
                [
                    'label' => 'Penjualan Bulan Ini',
                    'value' => 'Rp ' . number_format((float) $revenueMonth, 0, ',', '.'),
                    'tone' => 'success',
                    'meta' => 'Berdasarkan booking terkonfirmasi',
                ],
                [
                    'label' => 'Booking Masuk',
                    'value' => number_format((float) $bookingsMonth, 0, ',', '.'),
                    'tone' => 'info',
                    'meta' => 'Booking produk mitra bulan ini',
                ],
                [
                    'label' => 'Produk Aktif',
                    'value' => number_format((float) ((clone $productBase)->where('p.is_active', true)->count()), 0, ',', '.'),
                    'tone' => 'primary',
                    'meta' => 'Produk mitra yang sedang aktif',
                ],
                [
                    'label' => 'Perlu Perhatian',
                    'value' => number_format((float) $attentionCount, 0, ',', '.'),
                    'tone' => 'warning',
                    'meta' => 'Booking pending / menunggu pembayaran',
                ],
            ];
        } else {
            $revenueMonth = DB::table('payments as py')
                ->where('py.status', 'paid')
                ->whereBetween(DB::raw('DATE(COALESCE(py.paid_at, py.created_at))'), [$monthStart, $monthEnd])
                ->sum('py.amount');

            $bookingsMonth = (clone $bookingsForMonth)
                ->whereBetween(DB::raw('DATE(b.created_at)'), [$monthStart, $monthEnd])
                ->count();

            $activePartners = DB::table('mitra_profiles')
                ->where('status', 'active')
                ->count();

            $pendingPayments = DB::table('payments')
                ->where('status', 'pending')
                ->count();

            // Stats user baru dari kolom is_active & phone
            $totalUsers = DB::table('users')->where('is_active', true)->count();
            $newUsersThisMonth = DB::table('users')
                ->where('is_active', true)
                ->whereBetween(DB::raw('DATE(created_at)'), [$monthStart, $monthEnd])
                ->count();
            $usersWithPhone = DB::table('users')
                ->where('is_active', true)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->count();

            $recentBookings = DB::table('bookings as b')
                ->join('users as u', 'u.id', '=', 'b.user_id')
                ->select('b.booking_code', 'u.name as customer_name', 'b.visit_date', 'b.status', 'b.total_amount')
                ->orderByDesc('b.created_at')
                ->limit(6)
                ->get();

            $recentActivity = DB::table('payments as py')
                ->join('bookings as b', 'b.id', '=', 'py.booking_id')
                ->leftJoin('payment_methods as pm', 'pm.id', '=', 'py.payment_method_id')
                ->select('py.payment_code', 'b.booking_code', 'pm.name as payment_method_name', 'py.amount', 'py.status', 'py.created_at')
                ->orderByDesc('py.created_at')
                ->limit(6)
                ->get();

            $hero = [
                'eyebrow' => 'Control Center',
                'title' => 'Lihat kesehatan bisnis Capolaga secara cepat dan rapi.',
                'subtitle' => 'Ringkasan ini membantu tim admin memantau penjualan, booking, partner aktif, dan aktivitas operasional terbaru tanpa harus membuka banyak modul.',
            ];

            $stats = [
                [
                    'label' => 'Penjualan Bulan Ini',
                    'value' => 'Rp ' . number_format((float) $revenueMonth, 0, ',', '.'),
                    'tone' => 'success',
                    'meta' => 'Akumulasi pembayaran lunas',
                ],
                [
                    'label' => 'Booking Bulan Ini',
                    'value' => number_format((float) $bookingsMonth, 0, ',', '.'),
                    'tone' => 'info',
                    'meta' => 'Booking baru yang dibuat bulan ini',
                ],
                [
                    'label' => 'Produk Aktif',
                    'value' => number_format((float) ((clone $productBase)->where('p.is_active', true)->count()), 0, ',', '.'),
                    'tone' => 'primary',
                    'meta' => 'Produk yang siap dijual',
                ],
                [
                    'label' => 'Mitra Aktif',
                    'value' => number_format((float) $activePartners, 0, ',', '.'),
                    'tone' => 'warning',
                    'meta' => $pendingPayments . ' pembayaran masih pending',
                ],
                [
                    'label' => 'Member Terdaftar',
                    'value' => number_format((float) $totalUsers, 0, ',', '.'),
                    'tone' => 'secondary',
                    'meta' => '+' . $newUsersThisMonth . ' baru bulan ini',
                ],
            ];
        }

        $slotsToday = DB::table('product_slots as ps')
            ->join('products as p', 'p.id', '=', 'ps.product_id')
            ->whereDate('ps.slot_date', $today);
        $this->applyProductScope($slotsToday, $isMitra, $mitraId, 'p');
        $slotsToday = $slotsToday
            ->selectRaw('
                COUNT(*) as total_slots,
                SUM(ps.total_slots - ps.booked_slots) as remaining_capacity,
                SUM(CASE WHEN ps.is_blocked = 1 THEN 1 ELSE 0 END) as blocked_slots
            ')
            ->first();

        return view('backend.dashboard', [
            'hero' => $hero,
            'stats' => $stats,
            'bookingStatusCounts' => $bookingStatusCounts,
            'sourceBreakdown' => $sourceBreakdown,
            'topProducts' => $topProducts,
            'upcomingVisits' => $upcomingVisits,
            'recentBookings' => $recentBookings,
            'recentActivity' => $recentActivity,
            'slotsToday' => $slotsToday,
            'isMitra' => $isMitra,
            'todayLabel' => now()->translatedFormat('d F Y'),
            'totalProducts' => (clone $productBase)->count(),
            'averageRating' => (float) ((clone $productBase)->avg('p.rating_avg') ?? 0),
            'totalReviews' => (int) ((clone $productBase)->sum('p.review_count') ?? 0),
            'totalUsers' => $totalUsers ?? null,
            'newUsersThisMonth' => $newUsersThisMonth ?? null,
            'usersWithPhone' => $usersWithPhone ?? null,
            'pendingPayments' => $pendingPayments ?? null,
        ]);
    }

    private function applyProductScope(Builder $query, bool $isMitra, ?int $mitraId, string $alias = 'p'): void
    {
        if (! $isMitra) {
            return;
        }

        $query->where("{$alias}.mitra_id", $mitraId ?: 0);
    }

    private function applyBookingScope(Builder $query, bool $isMitra, ?int $mitraId, string $bookingAlias = 'b'): void
    {
        if (! $isMitra) {
            return;
        }

        $query->whereExists(function ($subQuery) use ($mitraId, $bookingAlias) {
            $subQuery->selectRaw('1')
                ->from('booking_items as bi')
                ->join('products as p', 'p.id', '=', 'bi.product_id')
                ->whereColumn('bi.booking_id', "{$bookingAlias}.id")
                ->where('p.mitra_id', $mitraId ?: 0);
        });
    }
}
