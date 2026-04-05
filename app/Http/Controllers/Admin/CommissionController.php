<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    private function mitraId(): ?int
    {
        $user = auth()->user();
        return $user->hasRole('Super Admin') ? null : $user->mitraProfile?->id;
    }

    public function index(Request $request)
    {
        $mitraId = $this->mitraId();

        $query = DB::table('commissions as c')
            ->join('mitra_profiles as mp', 'mp.id', '=', 'c.mitra_id')
            ->join('payments as py', 'py.id', '=', 'c.payment_id')
            ->join('bookings as b', 'b.id', '=', 'py.booking_id')
            ->join('booking_items as bi', 'bi.id', '=', 'c.booking_item_id')
            ->select(
                'c.id', 'c.gross_amount', 'c.commission_rate', 'c.commission_amount',
                'c.net_amount', 'c.status', 'c.settlement_ref', 'c.settled_at', 'c.created_at',
                'mp.id as mitra_id', 'mp.business_name',
                'b.booking_code', 'b.visit_date', 'b.id as booking_id',
                'bi.product_name_snapshot',
                'py.payment_code'
            )
            ->when($mitraId, fn($q) => $q->where('c.mitra_id', $mitraId))
            ->orderByDesc('c.created_at');

        if ($request->filled('mitra_id')) {
            $query->where('c.mitra_id', $request->mitra_id);
        }

        if ($request->filled('status')) {
            $query->where('c.status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('c.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('c.created_at', '<=', $request->date_to);
        }

        $commissions = $query->paginate(20)->withQueryString();

        $statusCounts = DB::table('commissions')
            ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = DB::table('commissions')
            ->selectRaw('
                SUM(gross_amount) as total_gross,
                SUM(commission_amount) as total_commission,
                SUM(net_amount) as total_net,
                COUNT(*) as total_records
            ')
            ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->first();

        $mitras = $mitraId
            ? DB::table('mitra_profiles')->where('id', $mitraId)->get(['id', 'business_name'])
            : DB::table('mitra_profiles')->where('status', 'active')->orderBy('business_name')->get(['id', 'business_name']);

        return view('backend.commissions.index', compact(
            'commissions', 'statusCounts', 'summary', 'mitras'
        ));
    }

    public function show(int $id)
    {
        $commission = DB::table('commissions as c')
            ->join('mitra_profiles as mp', 'mp.id', '=', 'c.mitra_id')
            ->join('payments as py', 'py.id', '=', 'c.payment_id')
            ->join('bookings as b', 'b.id', '=', 'py.booking_id')
            ->join('booking_items as bi', 'bi.id', '=', 'c.booking_item_id')
            ->select(
                'c.*',
                'mp.business_name', 'mp.bank_name', 'mp.bank_account_no', 'mp.bank_account_name',
                'b.booking_code', 'b.visit_date', 'b.id as booking_id',
                'bi.product_name_snapshot', 'bi.quantity', 'bi.unit_price',
                'py.payment_code', 'py.paid_at'
            )
            ->where('c.id', $id)
            ->first();

        abort_if(! $commission, 404);

        return view('backend.commissions.detail', compact('commission'));
    }

    /**
     * Tandai komisi sebagai settled (sudah dibayarkan ke mitra).
     */
    public function settle(Request $request, int $id)
    {
        $request->validate([
            'settlement_ref' => ['required', 'string', 'max:100'],
        ]);

        $commission = DB::table('commissions')->where('id', $id)->first();
        abort_if(! $commission, 404);
        abort_if(
            ! in_array($commission->status, ['pending', 'processed']),
            422,
            'Hanya komisi pending/processed yang bisa di-settle.'
        );

        DB::table('commissions')->where('id', $id)->update([
            'status'         => 'settled',
            'settlement_ref' => $request->settlement_ref,
            'settled_at'     => now(),
            'updated_at'     => now(),
        ]);

        return back()->with('success', 'Komisi berhasil ditandai sebagai settled.');
    }

    /**
     * Bulk settle — proses banyak komisi sekaligus.
     */
    public function bulkSettle(Request $request)
    {
        $request->validate([
            'commission_ids'   => ['required', 'array', 'min:1'],
            'commission_ids.*' => ['integer', 'exists:commissions,id'],
            'settlement_ref'   => ['required', 'string', 'max:100'],
        ]);

        $count = DB::table('commissions')
            ->whereIn('id', $request->commission_ids)
            ->whereIn('status', ['pending', 'processed'])
            ->update([
                'status'         => 'settled',
                'settlement_ref' => $request->settlement_ref,
                'settled_at'     => now(),
                'updated_at'     => now(),
            ]);

        return back()->with('success', "{$count} komisi berhasil di-settle.");
    }

    /**
     * Batalkan komisi.
     */
    public function cancel(int $id)
    {
        $commission = DB::table('commissions')->where('id', $id)->first();
        abort_if(! $commission, 404);
        abort_if($commission->status === 'settled', 422, 'Komisi yang sudah settled tidak bisa dibatalkan.');

        DB::table('commissions')->where('id', $id)->update([
            'status'     => 'cancelled',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Komisi berhasil dibatalkan.');
    }

    // ── Commission Tiers ──────────────────────────────────────────────────────

    public function tiers()
    {
        // Tier komisi adalah konfigurasi global — hanya Super Admin
        abort_if(! auth()->user()->hasRole('Super Admin'), 403, 'Hanya Super Admin yang bisa mengelola tier komisi.');

        $tiers = DB::table('commission_tiers')->orderBy('min_monthly_revenue')->get();
        return view('backend.commissions.tiers', compact('tiers'));
    }

    public function storeTier(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'min_monthly_revenue'   => ['required', 'numeric', 'min:0'],
            'commission_rate'       => ['required', 'numeric', 'min:0', 'max:100'],
            'subscription_discount' => ['required', 'numeric', 'min:0', 'max:100'],
            'description'           => ['nullable', 'string', 'max:300'],
        ]);

        DB::table('commission_tiers')->insert([
            'name'                  => $data['name'],
            'min_monthly_revenue'   => $data['min_monthly_revenue'],
            'commission_rate'       => $data['commission_rate'],
            'subscription_discount' => $data['subscription_discount'],
            'description'           => $data['description'] ?? null,
            'created_at'            => now(),
        ]);

        return back()->with('success', 'Tier komisi berhasil ditambahkan.');
    }

    public function updateTier(Request $request, int $id)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'min_monthly_revenue'   => ['required', 'numeric', 'min:0'],
            'commission_rate'       => ['required', 'numeric', 'min:0', 'max:100'],
            'subscription_discount' => ['required', 'numeric', 'min:0', 'max:100'],
            'description'           => ['nullable', 'string', 'max:300'],
        ]);

        DB::table('commission_tiers')->where('id', $id)->update($data + ['updated_at' => now()]);

        return back()->with('success', 'Tier komisi berhasil diperbarui.');
    }

    public function destroyTier(int $id)
    {
        DB::table('commission_tiers')->where('id', $id)->delete();
        return back()->with('success', 'Tier komisi berhasil dihapus.');
    }
}
