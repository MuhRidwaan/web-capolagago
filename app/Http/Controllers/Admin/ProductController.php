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
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'mitra', 'primaryImage', 'images'])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name');

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

        if ($request->filled('mitra_id')) {
            $query->where('mitra_id', $request->integer('mitra_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status') === 'active');
        }

        $products = $query->paginate(15)->withQueryString();

        return view('backend.products.index', [
            'products' => $products,
            'categories' => ProductCategory::query()->orderBy('name')->get(),
            'mitras' => MitraProfile::query()->where('status', 'active')->orderBy('business_name')->get(),
        ]);
    }

    public function create()
    {
        return view('backend.products.form', $this->formData(new Product(), 'Create Product', 'Create Product Form'));
    }

    public function store(Request $request)
    {
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
        $product->load(['images', 'activityTags', 'category', 'mitra']);

        return view('backend.products.form', $this->formData($product, 'Edit Product', 'Edit Product Form'));
    }

    public function update(Request $request, Product $product)
    {
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
        $currentCategoryId = $product->category_id;
        $currentMitraId = $product->mitra_id;

        return [
            'product' => $product,
            'pageTitle' => $pageTitle,
            'formTitle' => $formTitle,
            'categories' => ProductCategory::query()
                ->when($currentCategoryId, function ($query) use ($currentCategoryId) {
                    $query->where(function ($innerQuery) use ($currentCategoryId) {
                        $innerQuery
                            ->where('is_active', true)
                            ->orWhere('id', $currentCategoryId);
                    });
                }, function ($query) {
                    $query->where('is_active', true);
                })
                ->orderBy('name')
                ->get(),
            'mitras' => MitraProfile::query()
                ->when($currentMitraId, function ($query) use ($currentMitraId) {
                    $query->where(function ($innerQuery) use ($currentMitraId) {
                        $innerQuery
                            ->where('status', 'active')
                            ->orWhere('id', $currentMitraId);
                    });
                }, function ($query) {
                    $query->where('status', 'active');
                })
                ->orderBy('business_name')
                ->get(),
            'tagGroups' => ActivityTag::query()->orderBy('group_name')->orderBy('name')->get()->groupBy('group_name'),
            'priceLabels' => ['/malam', '/orang', '/sesi', '/unit'],
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
            'mitra_id' => ['nullable', 'exists:mitra_profiles,id'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:200', Rule::unique('products', 'slug')->ignore($product?->id)],
            'short_desc' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_label' => ['nullable', Rule::in(['/malam', '/orang', '/sesi', '/unit'])],
            'min_pax' => ['nullable', 'integer', 'min:1', 'max:255'],
            'max_pax' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'max_capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'duration_hours' => ['nullable', 'numeric', 'min:0', 'max:99.9'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_desc' => ['nullable', 'string', 'max:300'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'activity_tags' => ['nullable', 'array'],
            'activity_tags.*' => ['integer', 'exists:activity_tags,id'],
            'new_images' => ['nullable', 'array'],
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
            'mitra_id' => $data['mitra_id'] ?? null,
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
