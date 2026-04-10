<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $bookingSummary = $this->bookingQuery($user->id)->get();

        return view('frontend.profile', [
            'user' => $user,
            'bookingSummary' => $bookingSummary,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:150'],
        ]);

        $user->update([
            'name' => $validated['name'],
        ]);

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
}
