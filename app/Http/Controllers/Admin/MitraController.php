<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MitraProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MitraController extends Controller
{
    public function index(Request $request)
    {
        $query = MitraProfile::query()
            ->with(['user'])
            ->withCount('products')
            ->latest('id');

        if ($request->filled('q')) {
            $q = trim((string) $request->string('q'));

            $query->where(function ($builder) use ($q) {
                $builder
                    ->where('business_name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('contact_person', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($userQuery) use ($q) {
                        $userQuery
                            ->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $mitras = $query->paginate(15)->withQueryString();

        return view('backend.mitra.index', [
            'mitras' => $mitras,
            'statuses' => ['pending', 'active', 'inactive', 'suspended'],
        ]);
    }

    public function create()
    {
        return view('backend.mitra.form', [
            'mitra' => new MitraProfile(),
            'pageTitle' => 'Create Mitra',
            'formTitle' => 'Create Mitra Form',
            'statusOptions' => ['pending', 'active', 'inactive', 'suspended'],
            'subscriptionOptions' => ['free', 'basic', 'premium'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateMitra($request);

        DB::transaction(function () use ($request, $data) {
            $user = User::create([
                'name' => $data['user_name'],
                'email' => $data['email'],
                'password' => Str::password(12),
            ]);

            $user->syncRoles(['Mitra']);

            $logoPath = $request->hasFile('logo')
                ? upload_store($request->file('logo'), 'mitra-logos')
                : null;

            MitraProfile::create([
                'user_id' => $user->id,
                'business_name' => $data['business_name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'contact_person' => $data['contact_person'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'website' => $data['website'] ?? null,
                'logo_path' => $logoPath,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_no' => $data['bank_account_no'] ?? null,
                'bank_account_name' => $data['bank_account_name'] ?? null,
                'commission_rate' => $data['commission_rate'],
                'subscription_type' => $data['subscription_type'],
                'status' => $data['status'],
                'joined_at' => $data['joined_at'] ?? null,
            ]);
        });

        return redirect()->route('admin.mitra.index')
            ->with('success', 'Mitra berhasil ditambahkan.');
    }

    public function edit(MitraProfile $mitra)
    {
        $mitra->load('user');

        return view('backend.mitra.form', [
            'mitra' => $mitra,
            'pageTitle' => 'Edit Mitra',
            'formTitle' => 'Edit Mitra Form',
            'statusOptions' => ['pending', 'active', 'inactive', 'suspended'],
            'subscriptionOptions' => ['free', 'basic', 'premium'],
        ]);
    }

    public function update(Request $request, MitraProfile $mitra)
    {
        $data = $this->validateMitra($request, $mitra);

        DB::transaction(function () use ($request, $mitra, $data) {
            $mitra->user->update([
                'name' => $data['user_name'],
                'email' => $data['email'],
            ]);

            $logoPath = $mitra->logo_path;
            if ($request->boolean('remove_logo') && $logoPath) {
                upload_delete($logoPath);
                $logoPath = null;
            }

            if ($request->hasFile('logo')) {
                if ($logoPath) {
                    upload_delete($logoPath);
                }
                $logoPath = upload_store($request->file('logo'), 'mitra-logos');
            }

            $mitra->update([
                'business_name' => $data['business_name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'contact_person' => $data['contact_person'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'website' => $data['website'] ?? null,
                'logo_path' => $logoPath,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_no' => $data['bank_account_no'] ?? null,
                'bank_account_name' => $data['bank_account_name'] ?? null,
                'commission_rate' => $data['commission_rate'],
                'subscription_type' => $data['subscription_type'],
                'status' => $data['status'],
                'joined_at' => $data['joined_at'] ?? null,
            ]);
        });

        return redirect()->route('admin.mitra.index')
            ->with('success', 'Mitra berhasil diperbarui.');
    }

    public function updateStatus(Request $request, MitraProfile $mitra)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'active', 'inactive', 'suspended'])],
        ]);

        $mitra->update(['status' => $data['status']]);

        return back()->with('success', 'Status mitra berhasil diperbarui.');
    }

    public function destroy(MitraProfile $mitra)
    {
        if ($mitra->products()->exists()) {
            return back()->with('error', 'Mitra tidak bisa dihapus karena masih memiliki produk.');
        }

        DB::transaction(function () use ($mitra) {
            if ($mitra->logo_path) {
                upload_delete($mitra->logo_path);
            }

            $user = $mitra->user;
            $mitra->delete();
            $user?->delete();
        });

        return back()->with('success', 'Mitra berhasil dihapus.');
    }

    private function validateMitra(Request $request, ?MitraProfile $mitra = null): array
    {
        $userId = $mitra?->user_id;
        $slugBase = Str::slug((string) $request->input('business_name'));

        $request->merge([
            'slug' => $request->filled('slug') ? Str::slug((string) $request->input('slug')) : $slugBase,
            'commission_rate' => $request->input('commission_rate', 10),
            'subscription_type' => $request->input('subscription_type', 'free'),
            'status' => $request->input('status', 'pending'),
        ]);

        return $request->validate([
            'user_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'business_name' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:200', Rule::unique('mitra_profiles', 'slug')->ignore($mitra?->id)],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'subscription_type' => ['nullable', Rule::in(['free', 'basic', 'premium'])],
            'status' => ['nullable', Rule::in(['pending', 'active', 'inactive', 'suspended'])],
            'joined_at' => ['nullable', 'date'],
        ]);
    }
}
