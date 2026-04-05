<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', 'in:pending,paid,failed,expired,refunded'],
            'method_type' => ['nullable', 'in:manual,va,ewallet,qris,cc,cstore'],
        ]);

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $baseQuery = DB::table('payments as py')
            ->join('bookings as b', 'b.id', '=', 'py.booking_id')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->leftJoin('payment_methods as pm', 'pm.id', '=', 'py.payment_method_id')
            ->whereBetween(DB::raw('DATE(COALESCE(py.paid_at, py.created_at))'), [$dateFrom, $dateTo]);

        if ($request->filled('status')) {
            $baseQuery->where('py.status', $request->string('status'));
        }

        if ($request->filled('method_type')) {
            $baseQuery->where('pm.type', $request->string('method_type'));
        }

        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(CASE WHEN py.status = \'paid\' THEN py.amount ELSE 0 END) as total_paid,
                SUM(CASE WHEN py.status = \'refunded\' THEN py.amount ELSE 0 END) as total_refunded,
                SUM(CASE WHEN py.status = \'paid\' THEN py.fee_amount ELSE 0 END) as total_fees,
                AVG(CASE WHEN py.status = \'paid\' THEN py.amount END) as average_ticket
            ')
            ->first();

        $dailySales = (clone $baseQuery)
            ->selectRaw('
                DATE(COALESCE(py.paid_at, py.created_at)) as report_date,
                COUNT(*) as transactions_count,
                SUM(CASE WHEN py.status = \'paid\' THEN py.amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN py.status = \'refunded\' THEN py.amount ELSE 0 END) as refunded_amount
            ')
            ->groupBy(DB::raw('DATE(COALESCE(py.paid_at, py.created_at))'))
            ->orderBy('report_date')
            ->get();

        $payments = (clone $baseQuery)
            ->select(
                'py.payment_code',
                'py.amount',
                'py.fee_amount',
                'py.status',
                'py.paid_at',
                'py.created_at',
                'b.booking_code',
                'u.name as customer_name',
                'pm.name as payment_method_name',
                'pm.type as payment_method_type'
            )
            ->orderByDesc('py.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('backend.reports.sales', [
            'summary' => $summary,
            'dailySales' => $dailySales,
            'payments' => $payments,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'statuses' => ['pending', 'paid', 'failed', 'expired', 'refunded'],
            'methodTypes' => [
                'manual' => 'Manual',
                'va' => 'Virtual Account',
                'ewallet' => 'E-Wallet',
                'qris' => 'QRIS',
                'cc' => 'Kartu Kredit',
                'cstore' => 'Convenience Store',
            ],
        ]);
    }

    public function commissions(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', 'in:pending,processed,settled,cancelled'],
            'mitra_id' => ['nullable', 'exists:mitra_profiles,id'],
        ]);

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $baseQuery = DB::table('commissions as c')
            ->join('mitra_profiles as m', 'm.id', '=', 'c.mitra_id')
            ->join('payments as py', 'py.id', '=', 'c.payment_id')
            ->join('bookings as b', 'b.id', '=', 'py.booking_id')
            ->join('booking_items as bi', 'bi.id', '=', 'c.booking_item_id')
            ->join('products as p', 'p.id', '=', 'bi.product_id')
            ->whereBetween(DB::raw('DATE(COALESCE(c.settled_at, c.created_at))'), [$dateFrom, $dateTo]);

        if ($request->filled('status')) {
            $baseQuery->where('c.status', $request->string('status'));
        }

        if ($request->filled('mitra_id')) {
            $baseQuery->where('c.mitra_id', $request->integer('mitra_id'));
        }

        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total_rows,
                SUM(c.gross_amount) as total_gross,
                SUM(c.commission_amount) as total_commission,
                SUM(c.net_amount) as total_net
            ')
            ->first();

        $byMitra = (clone $baseQuery)
            ->selectRaw('
                m.business_name,
                COUNT(*) as total_items,
                SUM(c.gross_amount) as gross_amount,
                SUM(c.commission_amount) as commission_amount,
                SUM(c.net_amount) as net_amount
            ')
            ->groupBy('m.business_name')
            ->orderByDesc('commission_amount')
            ->get();

        $commissions = (clone $baseQuery)
            ->select(
                'c.id',
                'c.gross_amount',
                'c.commission_rate',
                'c.commission_amount',
                'c.net_amount',
                'c.status',
                'c.settled_at',
                'c.created_at',
                'c.settlement_ref',
                'm.business_name',
                'b.booking_code',
                'p.name as product_name',
                'bi.quantity'
            )
            ->orderByDesc('c.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('backend.reports.commissions', [
            'summary' => $summary,
            'byMitra' => $byMitra,
            'commissions' => $commissions,
            'mitras' => DB::table('mitra_profiles')->orderBy('business_name')->get(['id', 'business_name']),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'statuses' => ['pending', 'processed', 'settled', 'cancelled'],
        ]);
    }

    public function products(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
        ]);

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $baseQuery = DB::table('booking_items as bi')
            ->join('bookings as b', 'b.id', '=', 'bi.booking_id')
            ->join('products as p', 'p.id', '=', 'bi.product_id')
            ->leftJoin('product_categories as pc', 'pc.id', '=', 'p.category_id')
            ->leftJoin('mitra_profiles as m', 'm.id', '=', 'p.mitra_id')
            ->whereIn('b.status', ['confirmed', 'checked_in', 'completed'])
            ->whereBetween('b.visit_date', [$dateFrom, $dateTo]);

        if ($request->filled('category_id')) {
            $baseQuery->where('p.category_id', $request->integer('category_id'));
        }

        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(DISTINCT bi.product_id) as active_products,
                COUNT(DISTINCT bi.booking_id) as total_bookings,
                SUM(bi.quantity) as total_qty,
                SUM(bi.subtotal) as total_revenue
            ')
            ->first();

        $topCategories = (clone $baseQuery)
            ->selectRaw('
                COALESCE(pc.label, \'Tanpa Kategori\') as category_name,
                SUM(bi.subtotal) as total_revenue,
                SUM(bi.quantity) as total_qty
            ')
            ->groupBy(DB::raw('COALESCE(pc.label, \'Tanpa Kategori\')'))
            ->orderByDesc('total_revenue')
            ->get();

        $products = (clone $baseQuery)
            ->selectRaw('
                p.id,
                p.name as product_name,
                COALESCE(pc.label, \'-\') as category_name,
                COALESCE(m.business_name, \'Capolaga Internal\') as mitra_name,
                p.rating_avg,
                p.review_count,
                SUM(bi.quantity) as total_qty,
                COUNT(DISTINCT bi.booking_id) as booking_count,
                SUM(bi.subtotal) as total_revenue,
                AVG(bi.unit_price) as average_price
            ')
            ->groupBy('p.id', 'p.name', 'pc.label', 'm.business_name', 'p.rating_avg', 'p.review_count')
            ->orderByDesc('total_revenue')
            ->paginate(15)
            ->withQueryString();

        return view('backend.reports.products', [
            'summary' => $summary,
            'topCategories' => $topCategories,
            'products' => $products,
            'categories' => DB::table('product_categories')->orderBy('label')->get(['id', 'label']),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
