<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductSlotController extends Controller
{
    private function mitraId(): ?int
    {
        $user = auth()->user();
        return $user->hasRole('Super Admin') ? null : $user->mitraProfile?->id;
    }

    public function index(Request $request)
    {
        $mitraId = $this->mitraId();

        $products = DB::table('products')
            ->where('is_active', true)
            ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId))
            ->orderBy('name')
            ->get(['id', 'name', 'max_capacity']);

        $query = DB::table('product_slots as ps')
            ->join('products as p', 'p.id', '=', 'ps.product_id')
            ->select(
                'ps.id', 'ps.product_id', 'ps.slot_date', 'ps.start_time',
                'ps.total_slots', 'ps.booked_slots', 'ps.is_blocked',
                'ps.updated_at', 'p.name as product_name'
            )
            ->when($mitraId, fn($q) => $q->where('p.mitra_id', $mitraId));

        if ($request->filled('product_id')) {
            $query->where('ps.product_id', $request->product_id);
        }

        if ($request->filled('date_from')) {
            $query->where('ps.slot_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('ps.slot_date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'blocked'   => $query->where('ps.is_blocked', true),
                'available' => $query->where('ps.is_blocked', false)
                                     ->whereRaw('ps.booked_slots < ps.total_slots'),
                'full'      => $query->where('ps.is_blocked', false)
                                     ->whereRaw('ps.booked_slots >= ps.total_slots'),
                default     => null,
            };
        }

        $slots = $query->orderBy('ps.slot_date')->orderBy('ps.start_time')
            ->paginate(20)->withQueryString();

        return view('backend.slots.index', compact('slots', 'products'));
    }

    /**
     * Form create (single) & generate bulk.
     */
    public function create()
    {
        $mitraId = $this->mitraId();
        $products = DB::table('products')
            ->where('is_active', true)
            ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId))
            ->orderBy('name')
            ->get(['id', 'name', 'max_capacity']);

        return view('backend.slots.form', [
            'slot'     => null,
            'products' => $products,
        ]);
    }

    /**
     * Simpan single slot.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'  => ['required', 'exists:products,id'],
            'slot_date'   => ['required', 'date', 'after_or_equal:today'],
            'start_time'  => ['nullable', 'date_format:H:i'],
            'total_slots' => ['required', 'integer', 'min:1', 'max:9999'],
            'is_blocked'  => ['boolean'],
        ]);

        // Pastikan mitra hanya bisa buat slot untuk produknya sendiri
        $mitraId = $this->mitraId();
        if ($mitraId) {
            $owns = DB::table('products')->where('id', $data['product_id'])->where('mitra_id', $mitraId)->exists();
            abort_if(! $owns, 403, 'Kamu tidak memiliki akses ke produk ini.');
        }

        $exists = DB::table('product_slots')
            ->where('product_id', $data['product_id'])
            ->where('slot_date', $data['slot_date'])
            ->where('start_time', $data['start_time'] ?? null)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', 'Slot untuk produk, tanggal, dan jam tersebut sudah ada.');
        }

        DB::table('product_slots')->insert([
            'product_id'  => $data['product_id'],
            'slot_date'   => $data['slot_date'],
            'start_time'  => $data['start_time'] ?? null,
            'total_slots' => $data['total_slots'],
            'booked_slots'=> 0,
            'is_blocked'  => $request->boolean('is_blocked'),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('admin.slots.index')
            ->with('success', 'Slot berhasil ditambahkan.');
    }

    /**
     * Generate slot bulk (range tanggal × produk).
     */
    public function generate(Request $request)
    {
        $data = $request->validate([
            'product_id'  => ['required', 'exists:products,id'],
            'date_from'   => ['required', 'date', 'after_or_equal:today'],
            'date_to'     => ['required', 'date', 'after_or_equal:date_from'],
            'start_time'  => ['nullable', 'date_format:H:i'],
            'total_slots' => ['required', 'integer', 'min:1', 'max:9999'],
            'skip_days'   => ['nullable', 'array'],
            'skip_days.*' => ['integer', 'between:0,6'], // 0=Sun, 6=Sat
        ]);

        $mitraId = $this->mitraId();
        if ($mitraId) {
            $owns = DB::table('products')->where('id', $data['product_id'])->where('mitra_id', $mitraId)->exists();
            abort_if(! $owns, 403, 'Kamu tidak memiliki akses ke produk ini.');
        }

        $skipDays = $data['skip_days'] ?? [];
        $current  = \Carbon\Carbon::parse($data['date_from']);
        $end      = \Carbon\Carbon::parse($data['date_to']);
        $inserted = 0;
        $skipped  = 0;

        while ($current->lte($end)) {
            // Lewati hari yang dipilih (misal: Senin, Selasa)
            if (! in_array($current->dayOfWeek, $skipDays)) {
                $exists = DB::table('product_slots')
                    ->where('product_id', $data['product_id'])
                    ->where('slot_date', $current->toDateString())
                    ->where('start_time', $data['start_time'] ?? null)
                    ->exists();

                if (! $exists) {
                    DB::table('product_slots')->insert([
                        'product_id'   => $data['product_id'],
                        'slot_date'    => $current->toDateString(),
                        'start_time'   => $data['start_time'] ?? null,
                        'total_slots'  => $data['total_slots'],
                        'booked_slots' => 0,
                        'is_blocked'   => false,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                    $inserted++;
                } else {
                    $skipped++;
                }
            }

            $current->addDay();
        }

        return redirect()->route('admin.slots.index', ['product_id' => $data['product_id']])
            ->with('success', "{$inserted} slot berhasil digenerate." . ($skipped ? " {$skipped} slot dilewati (sudah ada)." : ''));
    }

    /**
     * Form edit single slot.
     */
    public function edit(int $id)
    {
        $slot = DB::table('product_slots')->where('id', $id)->first();
        abort_if(! $slot, 404);

        $mitraId = $this->mitraId();
        if ($mitraId) {
            $owns = DB::table('products')->where('id', $slot->product_id)->where('mitra_id', $mitraId)->exists();
            abort_if(! $owns, 403, 'Kamu tidak memiliki akses ke slot ini.');
        }

        $products = DB::table('products')
            ->where('is_active', true)
            ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId))
            ->orderBy('name')
            ->get(['id', 'name', 'max_capacity']);

        return view('backend.slots.form', compact('slot', 'products'));
    }

    /**
     * Update single slot.
     */
    public function update(Request $request, int $id)
    {
        $slot = DB::table('product_slots')->where('id', $id)->first();
        abort_if(! $slot, 404);

        $data = $request->validate([
            'total_slots' => ['required', 'integer', 'min:' . $slot->booked_slots],
            'start_time'  => ['nullable', 'date_format:H:i'],
            'is_blocked'  => ['boolean'],
        ], [
            'total_slots.min' => 'Total slot tidak boleh kurang dari slot yang sudah dipesan (' . $slot->booked_slots . ').',
        ]);

        DB::table('product_slots')->where('id', $id)->update([
            'total_slots' => $data['total_slots'],
            'start_time'  => $data['start_time'] ?? null,
            'is_blocked'  => $request->boolean('is_blocked'),
            'updated_at'  => now(),
        ]);

        return redirect()->route('admin.slots.index')
            ->with('success', 'Slot berhasil diperbarui.');
    }

    /**
     * Bulk update: ubah total_slots atau is_blocked untuk banyak slot sekaligus.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'slot_ids'    => ['required', 'array', 'min:1'],
            'slot_ids.*'  => ['integer', 'exists:product_slots,id'],
            'bulk_action' => ['required', 'in:set_slots,block,unblock'],
            'total_slots' => ['required_if:bulk_action,set_slots', 'nullable', 'integer', 'min:1'],
        ]);

        $ids = $request->slot_ids;

        match ($request->bulk_action) {
            'set_slots' => DB::table('product_slots')
                ->whereIn('id', $ids)
                ->update(['total_slots' => $request->total_slots, 'updated_at' => now()]),

            'block'   => DB::table('product_slots')
                ->whereIn('id', $ids)
                ->update(['is_blocked' => true, 'updated_at' => now()]),

            'unblock' => DB::table('product_slots')
                ->whereIn('id', $ids)
                ->update(['is_blocked' => false, 'updated_at' => now()]),
        };

        return back()->with('success', count($ids) . ' slot berhasil diperbarui.');
    }

    /**
     * Hapus slot (hanya jika belum ada booking).
     */
    public function destroy(int $id)
    {
        $slot = DB::table('product_slots')->where('id', $id)->first();
        abort_if(! $slot, 404);

        if ($slot->booked_slots > 0) {
            return back()->with('error', 'Slot tidak bisa dihapus karena sudah ada booking.');
        }

        DB::table('product_slots')->where('id', $id)->delete();

        return back()->with('success', 'Slot berhasil dihapus.');
    }

    /**
     * Bulk delete slot yang belum ada booking.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'slot_ids'   => ['required', 'array', 'min:1'],
            'slot_ids.*' => ['integer', 'exists:product_slots,id'],
        ]);

        $deleted = DB::table('product_slots')
            ->whereIn('id', $request->slot_ids)
            ->where('booked_slots', 0)
            ->delete();

        $skipped = count($request->slot_ids) - $deleted;

        return back()->with('success', "{$deleted} slot dihapus." . ($skipped ? " {$skipped} dilewati (ada booking)." : ''));
    }
}
