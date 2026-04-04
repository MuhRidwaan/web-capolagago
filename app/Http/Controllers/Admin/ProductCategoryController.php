<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductCategory::query()->withCount('products')->orderBy('sort_order')->orderBy('name');

        if ($request->filled('q')) {
            $q = trim((string) $request->string('q'));
            $query->where(function ($builder) use ($q) {
                $builder
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('label', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        $categories = $query->paginate(15)->withQueryString();

        return view('backend.product-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('backend.product-categories.form', ['category' => new ProductCategory()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCategory($request);
        ProductCategory::create($data);

        return redirect()->route('admin.product-categories.index')
            ->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('backend.product-categories.form', ['category' => $productCategory]);
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $data = $this->validateCategory($request, $productCategory);
        $productCategory->update($data);

        return redirect()->route('admin.product-categories.index')
            ->with('success', 'Kategori produk berhasil diperbarui.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        if ($productCategory->products()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih dipakai produk.');
        }

        $productCategory->delete();

        return back()->with('success', 'Kategori produk berhasil dihapus.');
    }

    private function validateCategory(Request $request, ?ProductCategory $category = null): array
    {
        $request->merge([
            'slug' => $request->filled('slug')
                ? Str::slug((string) $request->input('slug'))
                : Str::slug((string) $request->input('name')),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('product_categories', 'name')->ignore($category?->id)],
            'slug' => ['required', 'string', 'max:100', Rule::unique('product_categories', 'slug')->ignore($category?->id)],
            'label' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color_hex' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'type' => ['required', Rule::in(['internal', 'addon'])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
