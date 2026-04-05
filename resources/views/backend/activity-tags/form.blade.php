@extends('backend.main_backend')

@section('title', $tag->exists ? 'Edit Tag Aktivitas' : 'Tambah Tag Aktivitas')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $tag->exists ? 'Edit Tag Aktivitas' : 'Tambah Tag Aktivitas' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.activity-tags.index') }}">Tag Aktivitas</a></li>
                        <li class="breadcrumb-item active">{{ $tag->exists ? 'Edit' : 'Tambah' }}</li>
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
                    <h3 class="card-title mb-0">{{ $tag->exists ? 'Form Edit Tag Aktivitas' : 'Form Tambah Tag Aktivitas' }}</h3>
                </div>

                <div class="card-body">
                    <form action="{{ $tag->exists ? route('admin.activity-tags.update', $tag) : route('admin.activity-tags.store') }}" method="POST" id="activity-tag-form" data-swal-auto="true" data-swal-action="{{ $tag->exists ? 'update' : 'save' }}">
                        @csrf
                        @if ($tag->exists)
                            @method('PUT')
                        @endif

                        <input type="hidden" name="slug" id="tag-slug" value="{{ old('slug', $tag->slug) }}">

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Nama <span class="capolaga-required">*</span></label>
                                    <input type="text" name="name" id="tag-name" value="{{ old('name', $tag->name) }}" class="form-control capolaga-form-control @error('name') is-invalid @enderror" required>
                                    <small class="text-muted">Wajib diisi. Nama tag aktivitas yang akan dipakai di produk.</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Grup <span class="capolaga-required">*</span></label>
                                    <select name="group_name" class="form-control capolaga-form-control @error('group_name') is-invalid @enderror" required>
                                        <option value="audience" @selected(old('group_name', $tag->group_name ?: 'audience') === 'audience')>Audiens</option>
                                        <option value="difficulty" @selected(old('group_name', $tag->group_name) === 'difficulty')>Tingkat Kesulitan</option>
                                        <option value="facility" @selected(old('group_name', $tag->group_name) === 'facility')>Fasilitas</option>
                                        <option value="theme" @selected(old('group_name', $tag->group_name) === 'theme')>Tema</option>
                                    </select>
                                    <small class="text-muted">Wajib diisi. Pilih grup untuk mengelompokkan tag aktivitas.</small>
                                    @error('group_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="capolaga-form-footer">
                            <button type="submit" class="btn btn-primary capolaga-action-btn" id="activity-tag-submit-btn">{{ $tag->exists ? 'Perbarui' : 'Simpan' }}</button>
                            <a href="{{ route('admin.activity-tags.index') }}" class="btn btn-secondary capolaga-action-btn">Kembali</a>
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
            const form = document.getElementById('activity-tag-form');
            const submitButton = document.getElementById('activity-tag-submit-btn');
            const nameInput = document.getElementById('tag-name');
            const slugInput = document.getElementById('tag-slug');

            if (!form || !submitButton || !nameInput) {
                return;
            }

            const requiredFields = Array.from(form.querySelectorAll('[required]'));
            const isFieldFilled = (field) => field.value.trim() !== '';
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

            const syncSlug = () => {
                const slug = slugify(nameInput.value);

                if (slugInput) {
                    slugInput.value = slug;
                }
            };

            const updateButtonState = () => {
                return;
            };

            nameInput.addEventListener('input', syncSlug);
            nameInput.addEventListener('change', syncSlug);

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

            syncSlug();
            updateButtonState();
        });
    </script>
@endpush
