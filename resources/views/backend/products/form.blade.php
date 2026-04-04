@extends('backend.main_backend')

@section('title', $pageTitle)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">{{ $pageTitle }}</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Product Data</a></li>
                        <li class="breadcrumb-item active">{{ $product->exists ? 'Edit' : 'Create' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @include('backend.layouts.flash')

            <div class="card capolaga-form-card">
                <div class="card-header capolaga-form-header">
                    <h3 class="card-title mb-0">{{ $formTitle }}</h3>
                </div>

                <div class="card-body">
                    <form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form" data-swal-managed="custom">
                        @csrf
                        @if ($product->exists)
                            @method('PUT')
                        @endif

                        <input type="hidden" name="slug" id="product-slug" value="{{ old('slug', $product->slug) }}">

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Product Name <span class="capolaga-required">*</span></label>
                                    <input type="text" name="name" id="product-name" value="{{ old('name', $product->name) }}" class="form-control capolaga-form-control" required>
                                    <small class="text-muted">Wajib diisi. Nama produk yang akan tampil di admin dan website, misalnya <code>Glamping Riverside Luxury</code>.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Category <span class="capolaga-required">*</span></label>
                                    <select name="category_id" class="form-control capolaga-form-control" required>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Wajib diisi. Pilih kategori utama produk, misalnya glamping, rafting, atau ATV.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Mitra</label>
                                    <select name="mitra_id" class="form-control capolaga-form-control">
                                        <option value="">Capolaga Internal</option>
                                        @foreach ($mitras as $mitra)
                                            <option value="{{ $mitra->id }}" @selected((string) old('mitra_id', $product->mitra_id) === (string) $mitra->id)>{{ $mitra->business_name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Opsional. Kosongkan jika produk milik Capolaga langsung, pilih mitra jika produk dikelola vendor.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Price Label</label>
                                    <select name="price_label" class="form-control capolaga-form-control">
                                        @foreach ($priceLabels as $label)
                                            <option value="{{ $label }}" @selected(old('price_label', $product->price_label ?: '/malam') === $label)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Opsional. Jika tidak diubah, sistem akan memakai nilai default yang sudah dipilih.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Price <span class="capolaga-required">*</span></label>
                                    <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="form-control capolaga-form-control" required>
                                    <small class="text-muted">Wajib diisi. Masukkan angka harga tanpa titik atau simbol rupiah.</small>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Min Pax</label>
                                    <input type="number" name="min_pax" value="{{ old('min_pax', $product->min_pax ?: 1) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Default sistem: 1.</small>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Max Pax</label>
                                    <input type="number" name="max_pax" value="{{ old('max_pax', $product->max_pax ?: 10) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Default sistem: 10.</small>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Capacity</label>
                                    <input type="number" name="max_capacity" value="{{ old('max_capacity', $product->max_capacity ?: 1) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Default sistem: 1.</small>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Duration Hours</label>
                                    <input type="number" step="0.1" name="duration_hours" value="{{ old('duration_hours', $product->duration_hours) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Isi durasi aktivitas dalam jam, misalnya <code>2.5</code>.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Short Description</label>
                                    <input type="text" name="short_desc" value="{{ old('short_desc', $product->short_desc) }}" class="form-control capolaga-form-control" maxlength="300">
                                    <small class="text-muted">Opsional. Ringkasan singkat produk untuk preview kartu atau daftar produk.</small>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Sort Order</label>
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Default sistem: 0. Angka kecil akan tampil lebih dulu.</small>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="capolaga-form-label d-block">Flags</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                                        <label class="form-check-label" for="is_featured">Featured</label>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $product->exists ? $product->is_active : true))>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                    <small class="text-muted d-block mt-2">Opsional. Featured untuk produk unggulan, Active untuk menampilkan produk ke sistem.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Description</label>
                            <textarea name="description" rows="5" class="form-control">{{ old('description', $product->description) }}</textarea>
                            <small class="text-muted">Opsional. Deskripsi lengkap produk, fasilitas, pengalaman, atau informasi penting lain.</small>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Meta Title</label>
                                    <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Judul SEO untuk halaman produk di mesin pencari.</small>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Meta Description</label>
                                    <input type="text" name="meta_desc" value="{{ old('meta_desc', $product->meta_desc) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Ringkasan SEO singkat yang muncul di hasil pencarian.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Activity Tags</label>
                            <div class="row">
                                @foreach ($tagGroups as $group => $tags)
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="card border h-100">
                                            <div class="card-body">
                                                <h4 class="h6 font-weight-bold mb-3">{{ str($group)->headline() }}</h4>
                                                @foreach ($tags as $tag)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="tag_{{ $tag->id }}" name="activity_tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, old('activity_tags', $product->activityTags->pluck('id')->all()), true))>
                                                        <label class="form-check-label" for="tag_{{ $tag->id }}">{{ $tag->name }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Opsional. Pilih tag untuk membantu pengelompokan produk berdasarkan tema, tingkat kesulitan, atau target pengunjung.</small>
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Existing Images</label>
                            <div class="row">
                                @forelse ($product->images as $image)
                                    <div class="col-lg-4 mb-4">
                                        <div class="card h-100 border">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}" alt="{{ $image->alt_text }}" style="height:180px;object-fit:cover;" class="card-img-top">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Alt Text</label>
                                                    <input type="text" name="existing_images[{{ $image->id }}][alt_text]" value="{{ old("existing_images.{$image->id}.alt_text", $image->alt_text) }}" class="form-control form-control-sm">
                                                </div>
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Sort Order</label>
                                                    <input type="number" name="existing_images[{{ $image->id }}][sort_order]" value="{{ old("existing_images.{$image->id}.sort_order", $image->sort_order) }}" class="form-control form-control-sm">
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="primary_{{ $image->id }}" name="existing_images[{{ $image->id }}][is_primary]" value="1" @checked(old("existing_images.{$image->id}.is_primary", $image->is_primary))>
                                                    <label class="form-check-label" for="primary_{{ $image->id }}">Primary image</label>
                                                </div>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" id="delete_{{ $image->id }}" name="delete_images[]" value="{{ $image->id }}">
                                                    <label class="form-check-label text-danger" for="delete_{{ $image->id }}">Delete this image</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="text-muted mb-3">Belum ada gambar produk.</div>
                                    </div>
                                @endforelse
                            </div>
                            <small class="text-muted">Opsional. Atur gambar utama, alt text, dan urutan tampilan gambar produk di sini.</small>
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Upload New Images</label>
                            <input type="file" name="new_images[]" class="form-control-file" accept="image/*" multiple>
                            <small class="form-text text-muted">Opsional. Upload satu atau beberapa gambar baru sekaligus.</small>
                        </div>

                        <div class="capolaga-form-footer">
                            <button type="submit" class="btn btn-primary capolaga-action-btn" id="product-submit-btn" disabled>{{ $product->exists ? 'Update' : 'Save' }}</button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary capolaga-action-btn">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('product-form');
    const submitButton = document.getElementById('product-submit-btn');
    const nameInput = document.getElementById('product-name');
    const slugInput = document.getElementById('product-slug');
    let isSubmitting = false;

    if (!form || !submitButton) return;

    const requiredFields = Array.from(form.querySelectorAll('[required]'));
    const isFieldFilled = (field) => {
        if (field.type === 'checkbox' || field.type === 'radio') {
            return field.checked;
        }

        return field.value.trim() !== '';
    };

    const slugify = (value) => value
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    const updateButtonState = () => {
        const allFilled = requiredFields.every(isFieldFilled);
        submitButton.disabled = isSubmitting || !allFilled;
    };

    if (nameInput && slugInput) {
        const syncSlug = () => {
            slugInput.value = slugify(nameInput.value);
        };

        nameInput.addEventListener('input', syncSlug);
        nameInput.addEventListener('change', syncSlug);
        syncSlug();
    }

    requiredFields.forEach((field) => {
        field.addEventListener('input', updateButtonState);
        field.addEventListener('change', updateButtonState);
    });

    window.addEventListener('pageshow', function () {
        isSubmitting = false;
        if (typeof Swal !== 'undefined') Swal.close();
        updateButtonState();
    });

    form.addEventListener('submit', function (event) {
        if (isSubmitting || !requiredFields.every(isFieldFilled)) return;
        event.preventDefault();
        if (typeof Swal === 'undefined') {
            isSubmitting = true;
            updateButtonState();
            form.submit();
            return;
        }

        Swal.fire({
            title: '{{ $product->exists ? 'Update product?' : 'Create product?' }}',
            text: '{{ $product->exists ? 'Perubahan data produk akan langsung disimpan.' : 'Produk baru akan langsung disimpan.' }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ $product->exists ? 'Ya, update' : 'Ya, simpan' }}',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            confirmButtonColor: '#1f8fff',
            cancelButtonColor: '#6d7a86',
            showClass: { popup: 'capolaga-swal-show' },
            hideClass: { popup: 'capolaga-swal-hide' }
        }).then((result) => {
            if (!result.isConfirmed) return;
            isSubmitting = true;
            updateButtonState();
            Swal.fire({
                title: '{{ $product->exists ? 'Updating...' : 'Saving...' }}',
                text: 'Mohon tunggu, data produk sedang diproses.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });
            form.submit();
        });
    });

    updateButtonState();
});
</script>
@endpush
