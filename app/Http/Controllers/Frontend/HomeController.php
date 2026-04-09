<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $preferredFeaturedSlugs = collect([
            'glamping-riverside-luxury',
            'standard-camping-ground',
            'homestay-forest-view',
            'rafting-ciater-adventure',
        ]);

        $featuredProducts = Product::query()
            ->with(['category', 'primaryImage'])
            ->where('is_active', true)
            ->whereIn('slug', $preferredFeaturedSlugs)
            ->get();

        if ($featuredProducts->count() < 4) {
            $fallbackProducts = Product::query()
                ->with(['category', 'primaryImage'])
                ->where('is_active', true)
                ->whereNotIn('slug', $featuredProducts->pluck('slug'))
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(4 - $featuredProducts->count())
                ->get();

            $featuredProducts = $featuredProducts->concat($fallbackProducts);
        }

        $featuredProducts = $preferredFeaturedSlugs
            ->map(fn ($slug) => $featuredProducts->firstWhere('slug', $slug))
            ->filter()
            ->values();

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
            ->limit(12)
            ->get();

        $heroProducts = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(3)
            ->get();

        return view('frontend.home', [
            'featuredProducts' => $featuredProducts,
            'mainCategories' => $mainCategories,
            'addonProducts' => $addonProducts,
            'heroProducts' => $heroProducts,
        ]);
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
