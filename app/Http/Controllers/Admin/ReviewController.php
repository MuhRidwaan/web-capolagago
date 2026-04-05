<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    private function mitraId(): ?int
    {
        $user = auth()->user();
        return $user->hasRole('Super Admin') ? null : $user->mitraProfile?->id;
    }

    public function index(Request $request)
    {
        $mitraId = $this->mitraId();

        $query = DB::table('reviews as r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->join('products as p', 'p.id', '=', 'r.product_id')
            ->join('bookings as b', 'b.id', '=', 'r.booking_id')
            ->select(
                'r.id', 'r.rating', 'r.comment', 'r.is_published', 'r.created_at',
                'u.name as user_name', 'u.email as user_email',
                'p.id as product_id', 'p.name as product_name',
                'b.booking_code'
            )
            ->when($mitraId, fn($q) => $q->where('p.mitra_id', $mitraId))
            ->orderByDesc('r.created_at');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($w) => $w
                ->where('u.name', 'like', "%$q%")
                ->orWhere('p.name', 'like', "%$q%")
                ->orWhere('r.comment', 'like', "%$q%")
            );
        }

        if ($request->filled('rating')) {
            $query->where('r.rating', $request->rating);
        }

        if ($request->filled('status')) {
            $query->where('r.is_published', $request->status === 'published');
        }

        if ($request->filled('product_id')) {
            $query->where('r.product_id', $request->product_id);
        }

        $reviews = $query->paginate(15)->withQueryString();

        $products = DB::table('products')
            ->where('is_active', true)
            ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $ratingCounts = DB::table('reviews')
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $avgRating = DB::table('reviews')->where('is_published', true)->avg('rating');

        return view('backend.reviews.index', compact(
            'reviews', 'products', 'ratingCounts', 'avgRating'
        ));
    }

    public function show(int $id)
    {
        $mitraId = $this->mitraId();

        $review = DB::table('reviews as r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->join('products as p', 'p.id', '=', 'r.product_id')
            ->join('bookings as b', 'b.id', '=', 'r.booking_id')
            ->select(
                'r.*',
                'u.name as user_name', 'u.email as user_email',
                'p.name as product_name', 'p.id as product_id',
                'b.booking_code', 'b.visit_date'
            )
            ->when($mitraId, fn($q) => $q->where('p.mitra_id', $mitraId))
            ->where('r.id', $id)
            ->first();

        abort_if(! $review, 404);

        return view('backend.reviews.detail', compact('review'));
    }

    /**
     * Toggle publish/unpublish.
     */
    public function togglePublish(int $id)
    {
        $review = DB::table('reviews')->where('id', $id)->first();
        abort_if(! $review, 404);

        $newStatus = ! $review->is_published;

        DB::table('reviews')->where('id', $id)
            ->update(['is_published' => $newStatus, 'updated_at' => now()]);

        // Recalculate rating_avg & review_count di tabel products
        $this->recalcProductRating($review->product_id);

        $label = $newStatus ? 'dipublikasikan' : 'disembunyikan';

        return back()->with('success', "Ulasan berhasil {$label}.");
    }

    /**
     * Bulk toggle publish.
     */
    public function bulkToggle(Request $request)
    {
        $request->validate([
            'review_ids'   => ['required', 'array', 'min:1'],
            'review_ids.*' => ['integer', 'exists:reviews,id'],
            'action'       => ['required', 'in:publish,unpublish'],
        ]);

        $status = $request->action === 'publish';

        DB::table('reviews')
            ->whereIn('id', $request->review_ids)
            ->update(['is_published' => $status, 'updated_at' => now()]);

        // Recalculate semua produk yang terdampak
        $productIds = DB::table('reviews')
            ->whereIn('id', $request->review_ids)
            ->pluck('product_id')
            ->unique();

        foreach ($productIds as $pid) {
            $this->recalcProductRating($pid);
        }

        $count = count($request->review_ids);
        $label = $status ? 'dipublikasikan' : 'disembunyikan';

        return back()->with('success', "{$count} ulasan berhasil {$label}.");
    }

    public function destroy(int $id)
    {
        $review = DB::table('reviews')->where('id', $id)->first();
        abort_if(! $review, 404);

        DB::table('reviews')->where('id', $id)->delete();
        $this->recalcProductRating($review->product_id);

        return back()->with('success', 'Ulasan berhasil dihapus.');
    }

    /**
     * Recalculate rating_avg dan review_count di tabel products.
     */
    private function recalcProductRating(int $productId): void
    {
        $stats = DB::table('reviews')
            ->where('product_id', $productId)
            ->where('is_published', true)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')
            ->first();

        DB::table('products')->where('id', $productId)->update([
            'rating_avg'   => round($stats->avg_rating ?? 0, 2),
            'review_count' => $stats->total ?? 0,
            'updated_at'   => now(),
        ]);
    }
}
