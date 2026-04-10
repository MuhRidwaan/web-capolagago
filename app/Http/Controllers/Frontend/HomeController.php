<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $pendingGuestBooking = $this->resolvePendingGuestBooking();
        $searchQuery = trim((string) $request->query('q', ''));

        $preferredFeaturedSlugs = collect([
            'glamping-riverside-luxury',
            'standard-camping-ground',
            'homestay-forest-view',
            'rafting-ciater-adventure',
        ]);

        $featuredProductsQuery = Product::query()
            ->with(['category', 'primaryImage'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('type', 'internal'));

        if ($searchQuery !== '') {
            $featuredProducts = $featuredProductsQuery
                ->where(function ($query) use ($searchQuery) {
                    $query
                        ->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('slug', 'like', "%{$searchQuery}%")
                        ->orWhere('short_desc', 'like', "%{$searchQuery}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($searchQuery) {
                            $categoryQuery
                                ->where('name', 'like', "%{$searchQuery}%")
                                ->orWhere('label', 'like', "%{$searchQuery}%");
                        });
                })
                ->orderByDesc('is_featured')
                ->orderByDesc('rating_avg')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        } else {
            $featuredProducts = (clone $featuredProductsQuery)
                ->whereIn('slug', $preferredFeaturedSlugs)
                ->get();
        }

        if ($searchQuery === '' && $featuredProducts->count() < 4) {
            $fallbackProducts = Product::query()
                ->with(['category', 'primaryImage'])
                ->where('is_active', true)
                ->whereHas('category', fn ($query) => $query->where('type', 'internal'))
                ->whereNotIn('slug', $featuredProducts->pluck('slug'))
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(4 - $featuredProducts->count())
                ->get();

            $featuredProducts = $featuredProducts->concat($fallbackProducts);
        }

        if ($searchQuery === '') {
            $orderedPreferredProducts = $preferredFeaturedSlugs
                ->map(fn ($slug) => $featuredProducts->firstWhere('slug', $slug))
                ->filter()
                ->values();

            $fallbackProducts = $featuredProducts
                ->reject(fn ($product) => $preferredFeaturedSlugs->contains($product->slug))
                ->values();

            $featuredProducts = $orderedPreferredProducts
                ->concat($fallbackProducts)
                ->take(4)
                ->values();
        } else {
            $featuredProducts = $featuredProducts->values();
        }

        $mainCategories = ProductCategory::query()
            ->where('is_active', true)
            ->where('type', 'internal')
            ->orderBy('sort_order')
            ->get();

        $addonProducts = Product::query()
            ->with(['category', 'primaryImage'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('type', 'addon'))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $heroProducts = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(3)
            ->get();

        $heroStats = DB::table('reviews')
            ->where('is_published', true)
            ->selectRaw('AVG(rating) as average_rating, COUNT(*) as total_reviews')
            ->first();

        $travelerStats = DB::table('bookings')
            ->whereIn('status', ['confirmed', 'checked_in', 'completed'])
            ->selectRaw('COALESCE(SUM(total_guests), 0) as total_travelers')
            ->first();

        return view('frontend.home', [
            'featuredProducts' => $featuredProducts,
            'homeSearchQuery' => $searchQuery,
            'mainCategories' => $mainCategories,
            'addonProducts' => $addonProducts,
            'heroProducts' => $heroProducts,
            'heroAverageRating' => (float) ($heroStats->average_rating ?? 0),
            'heroTotalReviews' => (int) ($heroStats->total_reviews ?? 0),
            'heroTotalTravelers' => (int) ($travelerStats->total_travelers ?? 0),
            'pendingGuestBooking' => $pendingGuestBooking,
        ]);
    }

    private function resolvePendingGuestBooking(): ?array
    {
        $publicToken = (string) session('guest_pending_booking_token', '');

        if ($publicToken === '') {
            return null;
        }

        $booking = DB::table('bookings as b')
            ->join('payments as py', 'py.booking_id', '=', 'b.id')
            ->leftJoin('booking_items as bi', function ($join) {
                $join->on('bi.booking_id', '=', 'b.id')
                    ->where('bi.is_addon', '=', false);
            })
            ->select(
                'b.id',
                'b.booking_code',
                'b.public_token',
                'b.status as booking_status',
                'b.total_amount',
                'py.status as payment_status',
                'py.expired_at',
                'bi.product_name_snapshot as main_product_name'
            )
            ->where('b.public_token', $publicToken)
            ->latest('py.created_at')
            ->first();

        if (! $booking) {
            session()->forget('guest_pending_booking_token');

            return null;
        }

        $isPendingBooking = in_array($booking->booking_status, ['pending', 'waiting_payment'], true);
        $isPendingPayment = ($booking->payment_status ?? null) === 'pending';

        if (! $isPendingBooking || ! $isPendingPayment) {
            session()->forget('guest_pending_booking_token');

            return null;
        }

        return [
            'booking_code' => $booking->booking_code,
            'public_token' => $booking->public_token,
            'main_product_name' => $booking->main_product_name ?: 'Pesanan Anda',
            'total_amount' => (float) $booking->total_amount,
            'payment_status' => $booking->payment_status,
            'expires_at' => $booking->expired_at
                ? Carbon::parse($booking->expired_at)->timezone('Asia/Jakarta')
                : null,
        ];
    }

    public function wisata(Request $request)
    {
        $searchQuery = trim((string) $request->query('q', ''));
        $categoryQuery = trim((string) $request->query('category', 'all'));
        $sortQuery = trim((string) $request->query('sort', 'popular'));

        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->where('type', 'internal')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'label', 'color_hex']);

        $products = Product::query()
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
            ->when($sortQuery === 'price_low', fn ($query) => $query->orderBy('price'))
            ->when($sortQuery === 'price_high', fn ($query) => $query->orderByDesc('price'))
            ->when($sortQuery === 'rating', fn ($query) => $query->orderByDesc('rating_avg'))
            ->when(! in_array($sortQuery, ['price_low', 'price_high', 'rating'], true), function ($query) {
                $query
                    ->orderByDesc('is_featured')
                    ->orderByDesc('rating_avg')
                    ->orderBy('sort_order');
            })
            ->orderBy('name')
            ->get();

        return view('frontend.wisata', [
            'categories' => $categories,
            'products' => $products,
            'searchQuery' => $searchQuery,
            'selectedCategory' => $categoryQuery,
            'selectedSort' => $sortQuery,
        ]);
    }

    public function wisataDetail(Request $request, string $slug)
    {
        $product = Product::query()
            ->with(['category', 'mitra', 'primaryImage', 'images', 'activityTags'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('type', 'internal'))
            ->firstOrFail();

        $reviewSummary = DB::table('reviews')
            ->where('product_id', $product->id)
            ->where('is_published', true)
            ->selectRaw('COUNT(*) as total_reviews, AVG(rating) as average_rating')
            ->first();

        $recentReviews = DB::table('reviews as r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->where('r.product_id', $product->id)
            ->where('r.is_published', true)
            ->select([
                'r.rating',
                'r.comment',
                'r.created_at',
                'u.name as user_name',
            ])
            ->orderByDesc('r.created_at')
            ->limit(6)
            ->get();

        $parkingRates = [
            [
                'vehicle' => 'Motor',
                'price' => 5000,
                'note' => 'per kunjungan',
            ],
            [
                'vehicle' => 'Mobil',
                'price' => 10000,
                'note' => 'per kunjungan',
            ],
            [
                'vehicle' => 'Hiace / Elf',
                'price' => 25000,
                'note' => 'rombongan kecil',
            ],
            [
                'vehicle' => 'Bus wisata',
                'price' => 50000,
                'note' => 'rombongan besar',
            ],
        ];

        return view('frontend.wisata-detail', [
            'product' => $product,
            'reviewSummary' => $reviewSummary,
            'recentReviews' => $recentReviews,
            'parkingRates' => $parkingRates,
            'prefilledDate' => (string) $request->query('date', now()->toDateString()),
            'prefilledGuests' => max(1, (int) $request->query('guests', 2)),
        ]);
    }
}
