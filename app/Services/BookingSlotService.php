<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class BookingSlotService
{
    public function syncBookedSlotsForBooking(int $bookingId): void
    {
        $items = DB::table('booking_items')
            ->select('slot_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('booking_id', $bookingId)
            ->whereNotNull('slot_id')
            ->groupBy('slot_id')
            ->get();

        foreach ($items as $item) {
            $this->recalculateSlot((int) $item->slot_id);
        }
    }

    public function syncBookedSlotsForBookingCode(string $bookingCode): void
    {
        $bookingId = DB::table('bookings')
            ->where('booking_code', $bookingCode)
            ->value('id');

        if (! $bookingId) {
            return;
        }

        $this->syncBookedSlotsForBooking((int) $bookingId);
    }

    public function recalculateSlot(int $slotId): void
    {
        $confirmedStatuses = ['waiting_payment', 'confirmed', 'checked_in', 'completed'];

        $bookedQuantity = DB::table('booking_items as bi')
            ->join('bookings as b', 'b.id', '=', 'bi.booking_id')
            ->where('bi.slot_id', $slotId)
            ->whereIn('b.status', $confirmedStatuses)
            ->sum('bi.quantity');

        DB::table('product_slots')
            ->where('id', $slotId)
            ->update([
                'booked_slots' => (int) $bookedQuantity,
                'updated_at' => now(),
            ]);
    }
}
