<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('promotions as p')
            ->join('promo_types as pt', 'pt.id', '=', 'p.promo_type_id')
            ->select('p.*', 'pt.name as type_name', 'pt.discount_type')
            ->orderByDesc('p.created_at');

        if ($request->filled('q')) {
            $q = trim((string) $request->string('q'));
            $query->where(function ($b) use ($q) {
                $b->where('p.name', 'like', "%{$q}%")
                  ->orWhere('p.code', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('p.is_active', $request->string('status') === 'active');
        }

        if ($request->filled('type')) {
            $query->where('p.promo_type_id', $request->integer('type'));
        }

        $promotions = $query->paginate(15)->withQueryString();
        $promoTypes = DB::table('promo_types')->where('is_active', true)->get();

        return view('backend.promotions.index', compact('promotions', 'promoTypes'));
    }

    public function create()
    {
        $promoTypes = DB::table('promo_types')->where('is_active', true)->get();
        return view('backend.promotions.form', [
            'promo'      => null,
            'promoTypes' => $promoTypes,
            'pageTitle'  => 'Buat Promo Baru',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePromo($request);

        DB::table('promotions')->insert([
            'promo_type_id'      => $data['promo_type_id'],
            'name'               => $data['name'],
            'code'               => strtoupper($data['code']),
            'description'        => $data['description'] ?? null,
            'discount_value'     => $data['discount_value'],
            'max_discount_amount'=> $data['max_discount_amount'] ?? null,
            'min_order_amount'   => $data['min_order_amount'] ?? 0,
            'quota'              => $data['quota'] ?? null,
            'used_count'         => 0,
            'max_use_per_user'   => $data['max_use_per_user'] ?? 1,
            'is_active'          => $request->boolean('is_active', true),
            'valid_from'         => $data['valid_from'],
            'valid_until'        => $data['valid_until'],
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promo berhasil dibuat.');
    }

    public function edit(int $id)
    {
        $promo = DB::table('promotions')->where('id', $id)->first();
        abort_if(! $promo, 404);

        $promoTypes = DB::table('promo_types')->where('is_active', true)->get();
        return view('backend.promotions.form', [
            'promo'      => $promo,
            'promoTypes' => $promoTypes,
            'pageTitle'  => 'Edit Promo',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $promo = DB::table('promotions')->where('id', $id)->first();
        abort_if(! $promo, 404);

        $data = $this->validatePromo($request, $id);

        DB::table('promotions')->where('id', $id)->update([
            'promo_type_id'      => $data['promo_type_id'],
            'name'               => $data['name'],
            'code'               => strtoupper($data['code']),
            'description'        => $data['description'] ?? null,
            'discount_value'     => $data['discount_value'],
            'max_discount_amount'=> $data['max_discount_amount'] ?? null,
            'min_order_amount'   => $data['min_order_amount'] ?? 0,
            'quota'              => $data['quota'] ?? null,
            'max_use_per_user'   => $data['max_use_per_user'] ?? 1,
            'is_active'          => $request->boolean('is_active'),
            'valid_from'         => $data['valid_from'],
            'valid_until'        => $data['valid_until'],
            'updated_at'         => now(),
        ]);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $promo = DB::table('promotions')->where('id', $id)->first();
        abort_if(! $promo, 404);

        DB::table('promotions')->where('id', $id)->delete();

        return back()->with('success', 'Promo berhasil dihapus.');
    }

    public function toggleActive(int $id)
    {
        $promo = DB::table('promotions')->where('id', $id)->first();
        abort_if(! $promo, 404);

        DB::table('promotions')->where('id', $id)
            ->update(['is_active' => ! $promo->is_active, 'updated_at' => now()]);

        $status = ! $promo->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Promo \"{$promo->name}\" berhasil {$status}.");
    }

    // ── Promo Types ──────────────────────────────────────────────────────────

    public function types()
    {
        $types = DB::table('promo_types')->orderBy('id')->get();
        return view('backend.promotions.types', compact('types'));
    }

    private function validatePromo(Request $request, ?int $excludeId = null): array
    {
        return $request->validate([
            'promo_type_id'      => ['required', 'exists:promo_types,id'],
            'name'               => ['required', 'string', 'max:150'],
            'code'               => ['required', 'string', 'max:50', 'alpha_dash',
                                     Rule::unique('promotions', 'code')->ignore($excludeId)],
            'description'        => ['nullable', 'string', 'max:500'],
            'discount_value'     => ['required', 'numeric', 'min:0'],
            'max_discount_amount'=> ['nullable', 'numeric', 'min:0'],
            'min_order_amount'   => ['nullable', 'numeric', 'min:0'],
            'quota'              => ['nullable', 'integer', 'min:1'],
            'max_use_per_user'   => ['required', 'integer', 'min:1', 'max:255'],
            'is_active'          => ['boolean'],
            'valid_from'         => ['required', 'date'],
            'valid_until'        => ['required', 'date', 'after_or_equal:valid_from'],
        ]);
    }
}
