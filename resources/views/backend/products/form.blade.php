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
                        <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Data Produk</a></li>
                        <li class="breadcrumb-item active">{{ $product->exists ? 'Edit' : 'Tambah' }}</li>
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
                                    <label class="capolaga-form-label">Nama Produk <span class="capolaga-required">*</span></label>
                                    <input type="text" name="name" id="product-name" value="{{ old('name', $product->name) }}" class="form-control capolaga-form-control" required>
                                    <small class="text-muted">Wajib diisi. Nama produk yang akan tampil di admin dan website, misalnya <code>Glamping Riverside Luxury</code>.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Kategori <span class="capolaga-required">*</span></label>
                                    <select name="category_id" class="form-control capolaga-form-control" required>
                                        <option value="" @selected(! old('category_id', $product->category_id)) disabled>Pilih kategori produk</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Wajib diisi. Pilih kategori utama produk, misalnya glamping, rafting, atau ATV.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Mitra <span class="capolaga-required">*</span></label>
                                    @if($isMitra ?? false)
                                        {{-- Mitra: field terkunci, value otomatis dari profil --}}
                                        <input type="hidden" name="mitra_id" value="{{ $mitraProfile->id }}">
                                        <input type="text" class="form-control capolaga-form-control"
                                            value="{{ $mitraProfile->business_name }}" readonly disabled>
                                        <small class="text-muted">Produk akan otomatis terdaftar atas nama mitra kamu.</small>
                                    @else
                                        <select name="mitra_id" class="form-control capolaga-form-control" required>
                                            <option value="" @selected(old('mitra_id', $product->mitra_id ? (string) $product->mitra_id : 'internal') === '') disabled>Pilih kepemilikan produk</option>
                                            <option value="internal" @selected(old('mitra_id', $product->mitra_id ? (string) $product->mitra_id : 'internal') === 'internal')>Capolaga Internal</option>
                                            @foreach ($mitras as $mitra)
                                                <option value="{{ $mitra->id }}" @selected((string) old('mitra_id', $product->mitra_id) === (string) $mitra->id)>{{ $mitra->business_name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Pilih <code>Capolaga Internal</code> jika produk milik Capolaga langsung.</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Satuan Harga <span class="capolaga-required">*</span></label>
                                    <select name="price_label" class="form-control capolaga-form-control" required>
                                        @foreach ($priceLabels as $label)
                                            <option value="{{ $label }}" @selected(old('price_label', $product->price_label ?: '/malam') === $label)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Wajib diisi. Pilih satuan harga yang sesuai, misalnya per malam, per orang, per sesi, atau per unit.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Harga <span class="capolaga-required">*</span></label>
                                    <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="form-control capolaga-form-control" required>
                                    <small class="text-muted">Wajib diisi. Masukkan angka harga tanpa titik atau simbol rupiah.</small>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Min. Tamu <span class="capolaga-required">*</span></label>
                                    <input type="number" name="min_pax" value="{{ old('min_pax', $product->min_pax ?: 1) }}" class="form-control capolaga-form-control" min="1" required>
                                    <small class="text-muted">Wajib diisi. Tentukan minimal peserta atau tamu untuk produk ini.</small>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Maks. Tamu <span class="capolaga-required">*</span></label>
                                    <input type="number" name="max_pax" value="{{ old('max_pax', $product->max_pax ?: 10) }}" class="form-control capolaga-form-control" min="1" required>
                                    <small class="text-muted">Wajib diisi. Nilai ini harus sama atau lebih besar dari Min Pax.</small>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Kapasitas <span class="capolaga-required">*</span></label>
                                    <input type="number" name="max_capacity" value="{{ old('max_capacity', $product->max_capacity ?: 1) }}" class="form-control capolaga-form-control" min="1" required>
                                    <small class="text-muted">Wajib diisi. Jumlah unit atau slot yang tersedia per hari.</small>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Durasi (Jam) <span class="capolaga-required">*</span></label>
                                    <input type="number" step="0.1" min="0" name="duration_hours" value="{{ old('duration_hours', $product->duration_hours) }}" class="form-control capolaga-form-control" required>
                                    <small class="text-muted">Wajib diisi. Isi durasi aktivitas dalam jam, misalnya <code>2.5</code>.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Deskripsi Singkat <span class="capolaga-required">*</span></label>
                                    <input type="text" name="short_desc" value="{{ old('short_desc', $product->short_desc) }}" class="form-control capolaga-form-control" maxlength="300" required>
                                    <small class="text-muted">Wajib diisi. Ringkasan singkat produk untuk preview kartu atau daftar produk.</small>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Urutan Tampil <span class="capolaga-required">*</span></label>
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="form-control capolaga-form-control" min="0" required>
                                    <small class="text-muted">Wajib diisi. Angka kecil akan tampil lebih dulu.</small>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="capolaga-form-label d-block">Status Produk <span class="capolaga-required">*</span></label>
                                    <div class="mb-3">
                                        <label class="small font-weight-bold d-block">Status Unggulan</label>
                                        <select name="is_featured" class="form-control capolaga-form-control" required>
                                            <option value="" @selected(old('is_featured', $product->exists ? (string) (int) $product->is_featured : '') === '') disabled>Pilih status featured</option>
                                            <option value="1" @selected(old('is_featured', $product->exists ? (string) (int) $product->is_featured : '') === '1')>Unggulan</option>
                                            <option value="0" @selected(old('is_featured', $product->exists ? (string) (int) $product->is_featured : '') === '0')>Tidak Unggulan</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="small font-weight-bold d-block">Status Aktif</label>
                                        <select name="is_active" class="form-control capolaga-form-control" required>
                                            <option value="" @selected(old('is_active', $product->exists ? (string) (int) $product->is_active : '') === '') disabled>Pilih status produk</option>
                                            <option value="1" @selected(old('is_active', $product->exists ? (string) (int) $product->is_active : '') === '1')>Aktif</option>
                                            <option value="0" @selected(old('is_active', $product->exists ? (string) (int) $product->is_active : '') === '0')>Nonaktif</option>
                                        </select>
                                    </div>
                                    <small class="text-muted d-block mt-2">Wajib diisi. Tentukan apakah produk menjadi unggulan dan apakah produk aktif ditampilkan ke sistem.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Deskripsi <span class="capolaga-required">*</span></label>
                            <textarea name="description" rows="5" class="form-control" required>{{ old('description', $product->description) }}</textarea>
                            <small class="text-muted">Wajib diisi. Deskripsi lengkap produk, fasilitas, pengalaman, atau informasi penting lain.</small>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Meta Judul</label>
                                    <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Judul SEO untuk halaman produk di mesin pencari.</small>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Meta Deskripsi</label>
                                    <input type="text" name="meta_desc" value="{{ old('meta_desc', $product->meta_desc) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Ringkasan SEO singkat yang muncul di hasil pencarian.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" data-required-checkbox-group="activity_tags">
                            <label class="capolaga-form-label">Tag Aktivitas <span class="capolaga-required">*</span></label>
                            <div class="row">
                                @foreach ($tagGroups as $group => $tags)
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="card border h-100">
                                            <div class="card-body">
                                                <h4 class="h6 font-weight-bold mb-3">{{ str($group)->headline() }}</h4>
                                                @foreach ($tags as $tag)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="tag_{{ $tag->id }}" name="activity_tags[]" value="{{ $tag->id }}" data-checkbox-group="activity_tags" @checked(in_array($tag->id, old('activity_tags', $product->activityTags->pluck('id')->all()), true))>
                                                        <label class="form-check-label" for="tag_{{ $tag->id }}">{{ $tag->name }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Wajib diisi. Pilih minimal satu tag untuk membantu pengelompokan produk berdasarkan tema, tingkat kesulitan, atau target pengunjung.</small>
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Gambar Saat Ini</label>
                            <div class="row">
                                @forelse ($product->images as $image)
                                    <div class="col-lg-4 mb-4">
                                        <div class="card h-100 border">
                                            <img src="{{ upload_url($image->image_path) }}" alt="{{ $image->alt_text }}" style="height:180px;object-fit:cover;" class="card-img-top">
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
                                                    <label class="form-check-label" for="primary_{{ $image->id }}">Gambar utama</label>
                                                </div>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" id="delete_{{ $image->id }}" name="delete_images[]" value="{{ $image->id }}">
                                                    <label class="form-check-label text-danger" for="delete_{{ $image->id }}">Hapus gambar ini</label>
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
                            <label class="capolaga-form-label">Upload Gambar Baru @unless($product->exists)<span class="capolaga-required">*</span>@endunless</label>
                            <input type="file" name="new_images[]" class="form-control-file" accept="image/*" multiple @required(! $product->exists)>
                            <small class="form-text text-muted">
                                {{ $product->exists ? 'Opsional. Upload satu atau beberapa gambar baru sekaligus.' : 'Wajib diisi saat create product. Upload minimal satu gambar utama produk.' }}
                            </small>
                        </div>

                        <div class="capolaga-form-footer">
                            <button type="submit" class="btn btn-primary capolaga-action-btn" id="product-submit-btn">{{ $product->exists ? 'Perbarui' : 'Simpan' }}</button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary capolaga-action-btn">Kembali</a>
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
    const requiredCheckboxGroups = Array.from(form.querySelectorAll('[data-required-checkbox-group]'))
        .map((group) => ({
            fields: Array.from(group.querySelectorAll('input[type="checkbox"]')),
        }))
        .filter((group) => group.fields.length > 0);

    const isFieldFilled = (field) => {
        if (field.type === 'checkbox' || field.type === 'radio') {
            return field.checked;
        }

        if (field.type === 'file') {
            return field.files.length > 0;
        }

        return field.value.trim() !== '';
    };

    const syncFieldValidity = (field) => {
        field.setCustomValidity(isFieldFilled(field) ? '' : 'Wajib diisi.');
    };

    const slugify = (value) => value
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    const isCheckboxGroupFilled = (group) => group.fields.some((field) => field.checked);

    const syncCheckboxGroupValidity = (group) => {
        const firstField = group.fields[0];
        if (!firstField) return true;

        const isValid = isCheckboxGroupFilled(group);
        firstField.setCustomValidity(isValid ? '' : 'Wajib diisi.');

        return isValid;
    };

    const isFormComplete = () => {
        const allFieldsFilled = requiredFields.every(isFieldFilled);
        const allCheckboxGroupsFilled = requiredCheckboxGroups.every(syncCheckboxGroupValidity);

        return allFieldsFilled && allCheckboxGroupsFilled;
    };

    const updateButtonState = () => {
        requiredCheckboxGroups.forEach(syncCheckboxGroupValidity);
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
        field.addEventListener('input', function () {
            syncFieldValidity(field);
            updateButtonState();
        });
        field.addEventListener('change', function () {
            syncFieldValidity(field);
            updateButtonState();
        });
        field.addEventListener('invalid', function () {
            syncFieldValidity(field);
        });

        syncFieldValidity(field);
    });

    requiredCheckboxGroups.forEach((group) => {
        group.fields.forEach((field) => {
            field.addEventListener('input', updateButtonState);
            field.addEventListener('change', updateButtonState);
        });

        syncCheckboxGroupValidity(group);
    });

    window.addEventListener('pageshow', function () {
        isSubmitting = false;
        if (typeof Swal !== 'undefined') Swal.close();
        updateButtonState();
    });

    form.addEventListener('submit', function (event) {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        if (!isFormComplete() || !form.reportValidity()) {
            event.preventDefault();
            updateButtonState();
            return;
        }

        event.preventDefault();
        if (typeof Swal === 'undefined') {
            isSubmitting = true;
            updateButtonState();
            form.submit();
            return;
        }

        Swal.fire({
            title: '{{ $product->exists ? 'Perbarui produk?' : 'Tambah produk?' }}',
            text: '{{ $product->exists ? 'Perubahan data produk akan langsung disimpan.' : 'Produk baru akan langsung disimpan.' }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ $product->exists ? 'Ya, perbarui' : 'Ya, simpan' }}',
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
                title: '{{ $product->exists ? 'Memperbarui...' : 'Menyimpan...' }}',
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
