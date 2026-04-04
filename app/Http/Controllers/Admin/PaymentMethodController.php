<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    private array $types = [
        'va'      => 'Virtual Account',
        'ewallet' => 'E-Wallet',
        'qris'    => 'QRIS',
        'cc'      => 'Kartu Kredit/Debit',
        'cstore'  => 'Minimarket',
        'manual'  => 'Transfer Manual',
    ];

    private array $providers = [
        'midtrans' => 'Midtrans',
        'xendit'   => 'Xendit',
        'manual'   => 'Manual',
    ];

    public function index()
    {
        $methods = DB::table('payment_methods')->orderBy('sort_order')->get();
        return view('backend.payment-methods.index', [
            'methods'   => $methods,
            'types'     => $this->types,
            'providers' => $this->providers,
        ]);
    }

    public function create()
    {
        return view('backend.payment-methods.form', [
            'method'    => null,
            'types'     => $this->types,
            'providers' => $this->providers,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateMethod($request);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('payment-methods', 'public');
        }

        DB::table('payment_methods')->insert([
            'name'        => $data['name'],
            'code'        => strtoupper($data['code']),
            'provider'    => $data['provider'],
            'type'        => $data['type'],
            'logo_path'   => $logoPath,
            'fee_flat'    => $data['fee_flat'],
            'fee_percent' => $data['fee_percent'],
            'min_amount'  => $data['min_amount'],
            'max_amount'  => $data['max_amount'] ?: null,
            'is_active'   => $request->boolean('is_active'),
            'sort_order'  => $data['sort_order'],
        ]);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $method = DB::table('payment_methods')->where('id', $id)->first();
        abort_if(! $method, 404);

        return view('backend.payment-methods.form', [
            'method'    => $method,
            'types'     => $this->types,
            'providers' => $this->providers,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $method = DB::table('payment_methods')->where('id', $id)->first();
        abort_if(! $method, 404);

        $data = $this->validateMethod($request, $id);

        $logoPath = $method->logo_path;
        if ($request->hasFile('logo')) {
            // Hapus logo lama
            if ($logoPath) Storage::disk('public')->delete($logoPath);
            $logoPath = $request->file('logo')->store('payment-methods', 'public');
        }

        DB::table('payment_methods')->where('id', $id)->update([
            'name'        => $data['name'],
            'code'        => strtoupper($data['code']),
            'provider'    => $data['provider'],
            'type'        => $data['type'],
            'logo_path'   => $logoPath,
            'fee_flat'    => $data['fee_flat'],
            'fee_percent' => $data['fee_percent'],
            'min_amount'  => $data['min_amount'],
            'max_amount'  => $data['max_amount'] ?: null,
            'is_active'   => $request->boolean('is_active'),
            'sort_order'  => $data['sort_order'],
        ]);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    /**
     * Toggle aktif/nonaktif via AJAX atau form POST.
     */
    public function toggleActive(int $id)
    {
        $method = DB::table('payment_methods')->where('id', $id)->first();
        abort_if(! $method, 404);

        DB::table('payment_methods')->where('id', $id)
            ->update(['is_active' => ! $method->is_active]);

        $status = ! $method->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "{$method->name} berhasil {$status}.");
    }

    /**
     * Update sort_order via drag-and-drop (JSON body: [{id, sort_order}, ...]).
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'orders'             => ['required', 'array'],
            'orders.*.id'        => ['required', 'integer'],
            'orders.*.sort_order'=> ['required', 'integer'],
        ]);

        foreach ($request->orders as $item) {
            DB::table('payment_methods')
                ->where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function destroy(int $id)
    {
        $method = DB::table('payment_methods')->where('id', $id)->first();
        abort_if(! $method, 404);

        $inUse = DB::table('payments')->where('payment_method_id', $id)->exists();
        if ($inUse) {
            return back()->with('error', 'Metode ini tidak bisa dihapus karena sudah digunakan dalam transaksi.');
        }

        if ($method->logo_path) {
            Storage::disk('public')->delete($method->logo_path);
        }

        DB::table('payment_methods')->where('id', $id)->delete();

        return back()->with('success', 'Metode pembayaran berhasil dihapus.');
    }

    private function validateMethod(Request $request, ?int $excludeId = null): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:100',
                              Rule::unique('payment_methods', 'name')->ignore($excludeId)],
            'code'        => ['required', 'string', 'max:50',
                              Rule::unique('payment_methods', 'code')->ignore($excludeId)],
            'provider'    => ['required', 'in:midtrans,xendit,manual'],
            'type'        => ['required', 'in:va,ewallet,qris,cc,cstore,manual'],
            'logo'        => ['nullable', 'image', 'mimes:png,jpg,svg,webp', 'max:512'],
            'fee_flat'    => ['required', 'numeric', 'min:0'],
            'fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_amount'  => ['required', 'numeric', 'min:0'],
            'max_amount'  => ['nullable', 'numeric', 'min:0'],
            'sort_order'  => ['required', 'integer', 'min:0', 'max:255'],
            'is_active'   => ['boolean'],
        ]);
    }
}
