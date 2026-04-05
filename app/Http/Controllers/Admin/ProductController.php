<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityTag;
use App\Models\MitraProfile;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Ambil MitraProfile milik user yang sedang login.
     * Return null jika user adalah Super Admin (akses penuh).
     */
    private function currentMitraProfile(): ?MitraProfile
    {
        $user = auth()->user();
        if ($user->hasRole('Super Admin')) {
            return null;
        }
        return MitraProfile::where('user_id', $user->id)->first();
    }

    public function index(Request $request)
    {
        $mitraProfile = $this->currentMitraProfile();

        $query = Product::query()
            ->with(['category', 'mitra', 'primaryImage', 'images'])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name');

        // Mitra hanya lihat produk miliknya sendiri
        if ($mitraProfile) {
            $query->where('mitra_id', $mitraProfile->id);
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->string('q'));
            $query->where(function ($builder) use ($q) {
                $builder
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('short_desc', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        // Filter mitra hanya untuk Super Admin
        if (! $mitraProfile && $request->filled('mitra_id')) {
            $query->where('mitra_id', $request->integer('mitra_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status') === 'active');
        }

        $products = $query->paginate(15)->withQueryString();

        return view('backend.products.index', [
            'products'   => $products,
            'categories' => ProductCategory::query()->orderBy('name')->get(),
            // Mitra tidak perlu lihat dropdown filter mitra
            'mitras'     => $mitraProfile ? collect() : MitraProfile::query()->where('status', 'active')->orderBy('business_name')->get(),
            'isMitra'    => (bool) $mitraProfile,
        ]);
    }

    public function create()
    {
        return view('backend.products.form', $this->formData(new Product(), 'Tambah Produk', 'Form Tambah Produk'));
    }

    public function show(Product $product)
    {
        $mitraProfile = $this->currentMitraProfile();
        if ($mitraProfile && $product->mitra_id !== $mitraProfile->id) {
            abort(403);
        }

        $product->load(['images', 'activityTags', 'category', 'mitra']);

        $stats = DB::table('booking_items as bi')
            ->join('bookings as b', 'b.id', '=', 'bi.booking_id')
            ->where('bi.product_id', $product->id)
            ->whereIn('b.status', ['confirmed', 'checked_in', 'completed'])
            ->selectRaw('COUNT(DISTINCT b.id) as total_bookings, SUM(bi.quantity) as total_qty, SUM(bi.subtotal) as total_revenue')
            ->first();

        $recentReviews = DB::table('reviews as r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->where('r.product_id', $product->id)
            ->where('r.is_published', true)
            ->select('r.rating', 'r.comment', 'r.created_at', 'u.name as user_name')
            ->orderByDesc('r.created_at')
            ->limit(5)
            ->get();

        return view('backend.products.detail', compact('product', 'stats', 'recentReviews'));
    }

    public function store(Request $request)
    {
        $mitraProfile = $this->currentMitraProfile();

        // Paksa mitra_id ke profil mitra yang login
        if ($mitraProfile) {
            $request->merge(['mitra_id' => $mitraProfile->id]);
        }

        $data = $this->validateProduct($request);

        DB::transaction(function () use ($request, $data) {
            $product = Product::create($this->extractProductPayload($request, $data));
            $product->activityTags()->sync($data['activity_tags'] ?? []);
            $this->storeNewImages($request, $product);
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        // Mitra tidak boleh edit produk milik mitra lain
        $mitraProfile = $this->currentMitraProfile();
        if ($mitraProfile && $product->mitra_id !== $mitraProfile->id) {
            abort(403, 'Kamu tidak memiliki akses ke produk ini.');
        }

        $product->load(['images', 'activityTags', 'category', 'mitra']);

        return view('backend.products.form', $this->formData($product, 'Edit Product', 'Edit Product Form'));
    }

    public function update(Request $request, Product $product)
    {
        $mitraProfile = $this->currentMitraProfile();
        if ($mitraProfile && $product->mitra_id !== $mitraProfile->id) {
            abort(403, 'Kamu tidak memiliki akses ke produk ini.');
        }

        // Paksa mitra_id agar tidak bisa diubah oleh mitra
        if ($mitraProfile) {
            $request->merge(['mitra_id' => $mitraProfile->id]);
        }

        $product->load('images', 'activityTags');
        $data = $this->validateProduct($request, $product);

        DB::transaction(function () use ($request, $data, $product) {
            $product->update($this->extractProductPayload($request, $data));
            $product->activityTags()->sync($data['activity_tags'] ?? []);
            $this->syncExistingImages($request, $product);
            $this->storeNewImages($request, $product);
            $this->normalizePrimaryImage($product);
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $mitraProfile = $this->currentMitraProfile();
        if ($mitraProfile && $product->mitra_id !== $mitraProfile->id) {
            abort(403, 'Kamu tidak memiliki akses ke produk ini.');
        }

        try {
            $imagePaths = $product->images->pluck('image_path')->filter()->all();
            $product->delete();
            foreach ($imagePaths as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        } catch (QueryException) {
            return back()->with('error', 'Produk tidak bisa dihapus karena masih terhubung dengan data transaksi atau ulasan.');
        }

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    private function formData(Product $product, string $pageTitle, string $formTitle): array
    {
        $mitraProfile   = $this->currentMitraProfile();
        $currentCategoryId = $product->category_id;
        $currentMitraId    = $product->mitra_id;

        // Mitra hanya lihat profil miliknya sendiri
        $mitrasQuery = MitraProfile::query();
        if ($mitraProfile) {
            $mitrasQuery->where('id', $mitraProfile->id);
        } else {
            $mitrasQuery->when($currentMitraId, function ($q) use ($currentMitraId) {
                $q->where(function ($inner) use ($currentMitraId) {
                    $inner->where('status', 'active')->orWhere('id', $currentMitraId);
                });
            }, fn($q) => $q->where('status', 'active'));
        }

        return [
            'product'    => $product,
            'pageTitle'  => $pageTitle,
            'formTitle'  => $formTitle,
            'isMitra'    => (bool) $mitraProfile,
            'mitraProfile' => $mitraProfile,
            'categories' => ProductCategory::query()
                ->when($currentCategoryId, function ($q) use ($currentCategoryId) {
                    $q->where(fn($i) => $i->where('is_active', true)->orWhere('id', $currentCategoryId));
                }, fn($q) => $q->where('is_active', true))
                ->orderBy('name')
                ->get(),
            'mitras'     => $mitrasQuery->orderBy('business_name')->get(),
            'tagGroups'  => ActivityTag::query()->orderBy('group_name')->orderBy('name')->get()->groupBy('group_name'),
            'priceLabels'=> ['/malam', '/orang', '/sesi', '/unit'],
        ];
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $request->merge([
            'slug' => $request->filled('slug')
                ? Str::slug((string) $request->input('slug'))
                : Str::slug((string) $request->input('name')),
            'price_label' => $request->input('price_label', '/malam'),
            'min_pax' => $request->input('min_pax', 1),
            'max_pax' => $request->input('max_pax', 10),
            'max_capacity' => $request->input('max_capacity', 1),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return $request->validate([
            'mitra_id' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === 'internal') {
                        return;
                    }

                    if (! MitraProfile::query()->whereKey($value)->exists()) {
                        $fail('Pilihan mitra tidak valid.');
                    }
                },
            ],
            'category_id' => ['required', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:200', Rule::unique('products', 'slug')->ignore($product?->id)],
            'short_desc' => ['required', 'string', 'max:300'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_label' => ['required', Rule::in(['/malam', '/orang', '/sesi', '/unit'])],
            'min_pax' => ['required', 'integer', 'min:1', 'max:255'],
            'max_pax' => ['required', 'integer', 'min:1', 'max:1000', 'gte:min_pax'],
            'max_capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'duration_hours' => ['required', 'numeric', 'min:0', 'max:99.9'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_desc' => ['nullable', 'string', 'max:300'],
            'is_featured' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'activity_tags' => ['required', 'array', 'min:1'],
            'activity_tags.*' => ['integer', 'exists:activity_tags,id'],
            'new_images' => [$product ? 'nullable' : 'required', 'array'],
            'new_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'existing_images' => ['nullable', 'array'],
            'existing_images.*.alt_text' => ['nullable', 'string', 'max:200'],
            'existing_images.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:255'],
            'existing_images.*.is_primary' => ['nullable', 'boolean'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer', 'exists:product_images,id'],
        ]);
    }

    private function extractProductPayload(Request $request, array $data): array
    {
        return [
            'mitra_id' => ($data['mitra_id'] ?? null) === 'internal' ? null : $data['mitra_id'],
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'short_desc' => $data['short_desc'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'price_label' => $data['price_label'],
            'min_pax' => $data['min_pax'],
            'max_pax' => $data['max_pax'],
            'max_capacity' => $data['max_capacity'],
            'duration_hours' => $data['duration_hours'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'],
            'meta_title' => $data['meta_title'] ?? null,
            'meta_desc' => $data['meta_desc'] ?? null,
        ];
    }

    private function syncExistingImages(Request $request, Product $product): void
    {
        $deleteImageIds = collect($request->input('delete_images', []))->map(fn ($id) => (int) $id);

        foreach ($product->images as $image) {
            if ($deleteImageIds->contains($image->id)) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
                continue;
            }

            $imageData = $request->input("existing_images.{$image->id}", []);
            $image->update([
                'alt_text' => $imageData['alt_text'] ?? null,
                'sort_order' => (int) ($imageData['sort_order'] ?? $image->sort_order ?? 0),
                'is_primary' => $request->boolean("existing_images.{$image->id}.is_primary"),
            ]);
        }
    }

    private function storeNewImages(Request $request, Product $product): void
    {
        if (! $request->hasFile('new_images')) {
            $this->normalizePrimaryImage($product);
            return;
        }

        $nextSort = ((int) $product->images()->max('sort_order')) + 1;
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($request->file('new_images') as $index => $uploadedImage) {
            $path = $uploadedImage->store('products', 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'alt_text' => $product->name . ' image ' . ($index + 1),
                'is_primary' => ! $hasPrimary && $index === 0,
                'sort_order' => $nextSort + $index,
            ]);
        }

        $this->normalizePrimaryImage($product);
    }

    private function normalizePrimaryImage(Product $product): void
    {
        $images = $product->images()->orderByDesc('is_primary')->orderBy('sort_order')->get();

        if ($images->isEmpty()) {
            return;
        }

        $primaryId = $images->firstWhere('is_primary', true)?->id ?? $images->first()->id;

        $product->images()->update(['is_primary' => false]);
        $product->images()->whereKey($primaryId)->update(['is_primary' => true]);
    }
}
