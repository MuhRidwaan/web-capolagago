<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $bookingSummary = $this->bookingQuery($user->id)->get();
        $reviews = $this->reviewQuery($user->id)->get();

        return view('frontend.profile', [
            'user' => $user,
            'bookingSummary' => $bookingSummary,
            'reviews' => $reviews,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $hasPhoneColumn = Schema::hasColumn('users', 'phone');

        $request->merge([
            'phone' => preg_replace('/\s+/', '', (string) $request->input('phone', '')),
        ]);

        $rules = [
            'name' => ['required', 'string', 'min:3', 'max:150'],
        ];

        if ($hasPhoneColumn) {
            $rules['phone'] = ['required', 'string', 'regex:/^08[0-9]{8,13}$/'];
        }

        $validated = $request->validate($rules);

        $attributes = [
            'name' => $validated['name'],
        ];

        if ($hasPhoneColumn) {
            $attributes['phone'] = $validated['phone'];
        }

        $user->update($attributes);

        return back()->with('success_profile', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success_password', 'Password berhasil diperbarui.');
    }

    public function updateReview(Request $request, int $review)
    {
        $user = $request->user();

        $validated = $request->validateWithBag('updateReview', [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $reviewData = DB::table('reviews as r')
            ->join('products as p', 'p.id', '=', 'r.product_id')
            ->select(
                'r.id',
                'r.product_id',
                'r.user_id',
                'p.name as product_name'
            )
            ->where('r.id', $review)
            ->where('r.user_id', $user->id)
            ->first();

        abort_if(! $reviewData, 404);

        $comment = trim((string) ($validated['comment'] ?? ''));

        DB::table('reviews')
            ->where('id', $reviewData->id)
            ->update([
                'rating' => (int) $validated['rating'],
                'comment' => $comment !== '' ? $comment : null,
                'is_published' => true,
                'updated_at' => now(),
            ]);

        $this->recalcProductRating((int) $reviewData->product_id);

        return redirect()
            ->route('frontend.profile')
            ->with('success_review', 'Ulasan untuk ' . $reviewData->product_name . ' berhasil diperbarui.');
    }

    public function orders()
    {
        $user = Auth::user();

        $bookings = $this->bookingQuery($user->id)
            ->orderByDesc('b.created_at')
            ->get();

        $bookingItems = DB::table('booking_items')
            ->select('booking_id', 'product_name_snapshot', 'quantity', 'is_addon')
            ->whereIn('booking_id', $bookings->pluck('id'))
            ->orderBy('is_addon')
            ->orderBy('id')
            ->get()
            ->groupBy('booking_id');

        $bookings = $bookings->map(function ($booking) use ($bookingItems) {
            $items = $bookingItems->get($booking->id, collect());

            $booking->items = $items;
            $booking->main_package = optional($items->firstWhere('is_addon', 0))->product_name_snapshot
                ?? optional($items->first())->product_name_snapshot
                ?? 'Paket wisata';

            return $booking;
        });

        return view('frontend.orders', [
            'user' => $user,
            'bookings' => $bookings,
            'statusLabels' => [
                'pending' => 'Pending',
                'waiting_payment' => 'Menunggu Pembayaran',
                'confirmed' => 'Terkonfirmasi',
                'checked_in' => 'Check-in',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                'refunded' => 'Refunded',
                'paid' => 'Lunas',
                'failed' => 'Gagal',
                'expired' => 'Kedaluwarsa',
            ],
        ]);
    }

    private function bookingQuery(int $userId)
    {
        return DB::table('bookings as b')
            ->leftJoin('payments as py', function ($join) {
                $join->on('py.booking_id', '=', 'b.id')
                    ->whereRaw('py.id = (select max(p2.id) from payments as p2 where p2.booking_id = b.id)');
            })
            ->select(
                'b.id',
                'b.booking_code',
                'b.public_token',
                'b.visit_date',
                'b.checkout_date',
                'b.total_guests',
                'b.total_amount',
                'b.status',
                'b.created_at',
                'py.status as payment_status',
                'py.payment_code'
            )
            ->where('b.user_id', $userId);
    }

    private function reviewQuery(int $userId)
    {
        return DB::table('reviews as r')
            ->join('products as p', 'p.id', '=', 'r.product_id')
            ->join('bookings as b', 'b.id', '=', 'r.booking_id')
            ->select(
                'r.id',
                'r.rating',
                'r.comment',
                'r.is_published',
                'r.created_at',
                'r.updated_at',
                'p.name as product_name',
                'p.slug as product_slug',
                'b.booking_code',
                'b.public_token',
                'b.visit_date'
            )
            ->where('r.user_id', $userId)
            ->orderByDesc('r.updated_at');
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
}
