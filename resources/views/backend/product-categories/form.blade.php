@extends('backend.main_backend')

@section('title', $category->exists ? 'Edit Category' : 'Create Category')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $category->exists ? 'Edit Category' : 'Create Category' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.product-categories.index') }}">Product Categories</a></li>
                        <li class="breadcrumb-item active">{{ $category->exists ? 'Edit' : 'Create' }}</li>
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
                    <h3 class="card-title mb-0">{{ $category->exists ? 'Edit Category Form' : 'Create Category Form' }}</h3>
                </div>

                <div class="card-body">
                    <form action="{{ $category->exists ? route('admin.product-categories.update', $category) : route('admin.product-categories.store') }}" method="POST" id="product-category-form" data-swal-auto="true" data-swal-action="{{ $category->exists ? 'update' : 'save' }}">
                        @csrf
                        @if ($category->exists)
                            @method('PUT')
                        @endif

                        <input type="hidden" name="slug" id="category-slug" value="{{ old('slug', $category->slug) }}">
                        <input type="hidden" name="icon" value="{{ old('icon', $category->icon) }}">
                        <input type="hidden" name="color_hex" value="{{ old('color_hex', $category->color_hex) }}">

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Category Name <span class="capolaga-required">*</span></label>
                                    <input type="text" name="name" id="category-name" value="{{ old('name', $category->name) }}" class="form-control capolaga-form-control @error('name') is-invalid @enderror" required>
                                    <small class="text-muted">Wajib diisi. Nama kategori utama di sistem, misalnya <code>glamping</code> atau <code>rafting</code>.</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Display Name <span class="capolaga-required">*</span></label>
                                    <input type="text" name="label" id="category-label" value="{{ old('label', $category->label) }}" class="form-control capolaga-form-control @error('label') is-invalid @enderror" required>
                                    <small class="text-muted">Wajib diisi. Nama yang akan ditampilkan ke admin atau pengguna di website/app.</small>
                                    @error('label')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Category Type <span class="capolaga-required">*</span></label>
                                    <select name="type" class="form-control capolaga-form-control @error('type') is-invalid @enderror" required>
                                        <option value="internal" @selected(old('type', $category->type ?: 'internal') === 'internal')>Internal</option>
                                        <option value="addon" @selected(old('type', $category->type) === 'addon')>Addon</option>
                                    </select>
                                    <small class="text-muted">Wajib diisi. <code>Internal</code> untuk produk utama, <code>Addon</code> untuk tambahan.</small>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Display Order</label>
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="form-control capolaga-form-control @error('sort_order') is-invalid @enderror" min="0">
                                    <small class="text-muted">Opsional. Angka kecil akan tampil lebih dulu.</small>
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label d-block">Status</label>
                                    <div class="form-check mt-2 pt-1">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
                                        <label class="form-check-label" for="is_active">Active category</label>
                                    </div>
                                    <small class="text-muted d-block mt-2">Opsional. Nonaktifkan jika kategori belum ingin ditampilkan.</small>
                                </div>
                            </div>
                        </div>

                        <div class="capolaga-form-footer mt-4">
                            <button type="submit" class="btn btn-primary capolaga-action-btn" id="product-category-submit-btn">{{ $category->exists ? 'Update' : 'Save' }}</button>
                            <a href="{{ route('admin.product-categories.index') }}" class="btn btn-secondary capolaga-action-btn">Back</a>
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
            const form = document.getElementById('product-category-form');
            const submitButton = document.getElementById('product-category-submit-btn');
            const nameInput = document.getElementById('category-name');
            const labelInput = document.getElementById('category-label');
            const slugInput = document.getElementById('category-slug');

            if (!form || !submitButton || !nameInput || !slugInput) {
                return;
            }

            const requiredFields = Array.from(form.querySelectorAll('[required]'));
            const isFieldFilled = (field) => field.value.trim() !== '';
            const syncFieldValidity = (field) => {
                field.setCustomValidity(isFieldFilled(field) ? '' : 'Wajib diisi.');
            };
            let labelTouched = labelInput ? labelInput.value.trim() !== '' : true;

            const slugify = (value) => value
                .toString()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');

            const syncGeneratedFields = () => {
                const nameValue = nameInput.value.trim();
                slugInput.value = slugify(nameValue);

                if (labelInput && !labelTouched) {
                    labelInput.value = nameValue;
                }
            };

            const updateButtonState = () => {
                return;
            };

            if (labelInput) {
                labelInput.addEventListener('input', function () {
                    labelTouched = this.value.trim() !== '' && this.value.trim() !== nameInput.value.trim();
                });
            }

            nameInput.addEventListener('input', syncGeneratedFields);
            nameInput.addEventListener('change', syncGeneratedFields);

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

            syncGeneratedFields();
            updateButtonState();
        });
    </script>
@endpush
