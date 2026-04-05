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
                        <li class="breadcrumb-item"><a href="{{ route('admin.mitra.index') }}">Data Mitra</a></li>
                        <li class="breadcrumb-item active">{{ $mitra->exists ? 'Edit' : 'Tambah' }}</li>
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
                    <form action="{{ $mitra->exists ? route('admin.mitra.update', $mitra) : route('admin.mitra.store') }}" method="POST" enctype="multipart/form-data" id="mitra-form" data-swal-managed="custom">
                        @csrf
                        @if ($mitra->exists)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Nama Akun <span class="capolaga-required">*</span></label>
                                    <input type="text" name="user_name" value="{{ old('user_name', $mitra->user?->name) }}" class="form-control capolaga-form-control" required>
                                    <small class="text-muted">Wajib diisi. Nama akun user yang terhubung dengan mitra ini.</small>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Email <span class="capolaga-required">*</span></label>
                                    <input type="email" name="email" value="{{ old('email', $mitra->user?->email) }}" class="form-control capolaga-form-control" required>
                                    <small class="text-muted">Wajib diisi. Email login untuk akun mitra.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Nama Bisnis <span class="capolaga-required">*</span></label>
                                    <input type="text" name="business_name" value="{{ old('business_name', $mitra->business_name) }}" class="form-control capolaga-form-control" required>
                                    <small class="text-muted">Wajib diisi. Nama bisnis atau nama vendor yang akan tampil di sistem.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Deskripsi</label>
                            <textarea name="description" rows="4" class="form-control">{{ old('description', $mitra->description) }}</textarea>
                            <small class="text-muted">Opsional. Isi deskripsi singkat tentang mitra atau layanan yang ditawarkan.</small>
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Alamat</label>
                            <textarea name="address" rows="3" class="form-control">{{ old('address', $mitra->address) }}</textarea>
                            <small class="text-muted">Opsional. Alamat lengkap lokasi mitra.</small>
                        </div>

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Lintang</label>
                                    <input type="text" name="latitude" value="{{ old('latitude', $mitra->latitude) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Isi jika ingin menandai lokasi di peta.</small>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Bujur</label>
                                    <input type="text" name="longitude" value="{{ old('longitude', $mitra->longitude) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Pasangkan dengan latitude untuk koordinat lokasi.</small>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Kontak Person</label>
                                    <input type="text" name="contact_person" value="{{ old('contact_person', $mitra->contact_person) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Nama PIC atau kontak utama dari mitra.</small>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="capolaga-form-label">WhatsApp</label>
                                    <input type="text" name="whatsapp" value="{{ old('whatsapp', $mitra->whatsapp) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Nomor WhatsApp yang bisa dihubungi.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Website</label>
                                    <input type="url" name="website" value="{{ old('website', $mitra->website) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Isi jika mitra punya website resmi.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Langganan</label>
                                    <select name="subscription_type" class="form-control capolaga-form-control">
                                        @foreach ($subscriptionOptions as $option)
                                            <option value="{{ $option }}" @selected(old('subscription_type', $mitra->subscription_type ?: 'free') === $option)>{{ str($option)->headline() }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Opsional. Jika tidak diubah, sistem memakai default <code>Free</code>.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Status</label>
                                    <select name="status" class="form-control capolaga-form-control">
                                        @foreach ($statusOptions as $option)
                                            <option value="{{ $option }}" @selected(old('status', $mitra->status ?: 'pending') === $option)>{{ str($option)->headline() }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Opsional. Jika tidak diubah, sistem memakai default <code>Pending</code>.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Rate Komisi (%)</label>
                                    <input type="number" step="0.01" name="commission_rate" value="{{ old('commission_rate', $mitra->commission_rate ?: 10) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Jika kosong, sistem memakai default 10%.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Bergabung Sejak</label>
                                    <input type="date" name="joined_at" value="{{ old('joined_at', $mitra->joined_at?->format('Y-m-d')) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Tanggal mulai kerja sama dengan mitra.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Logo</label>
                                    <input type="file" name="logo" class="form-control-file" accept="image/*">
                                    <small class="text-muted d-block mb-2">Opsional. Upload logo mitra jika tersedia.</small>
                                    @if ($mitra->logo_path)
                                        <div class="mt-2">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($mitra->logo_path) }}" alt="Logo" style="max-width:110px;border-radius:.5rem;">
                                            <div class="form-check mt-2">
                                                <input type="checkbox" class="form-check-input" id="remove_logo" name="remove_logo" value="1">
                                                <label class="form-check-label" for="remove_logo">Hapus logo saat ini</label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Nama Bank</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name', $mitra->bank_name) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Nama bank rekening pencairan mitra.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Nomor Rekening</label>
                                    <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $mitra->bank_account_no) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Nomor rekening bank mitra.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="capolaga-form-label">Nama Akun</label>
                                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $mitra->bank_account_name) }}" class="form-control capolaga-form-control">
                                    <small class="text-muted">Opsional. Nama pemilik rekening.</small>
                                </div>
                            </div>
                        </div>

                        <div class="capolaga-form-footer">
                            <button type="submit" class="btn btn-primary capolaga-action-btn" id="mitra-submit-btn">{{ $mitra->exists ? 'Perbarui' : 'Simpan' }}</button>
                            <a href="{{ route('admin.mitra.index') }}" class="btn btn-secondary capolaga-action-btn">Kembali</a>
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
    const form = document.getElementById('mitra-form');
    const submitButton = document.getElementById('mitra-submit-btn');
    let isSubmitting = false;

    if (!form || !submitButton) return;

    const requiredFields = Array.from(form.querySelectorAll('[required]'));
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

    const updateButtonState = () => {
        return;
    };

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

        if (!form.reportValidity()) {
            event.preventDefault();
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
            title: '{{ $mitra->exists ? 'Perbarui mitra?' : 'Tambah mitra?' }}',
            text: '{{ $mitra->exists ? 'Perubahan data mitra akan langsung disimpan.' : 'Mitra baru akan langsung disimpan.' }}',
            icon: 'question', showCancelButton: true,
            confirmButtonText: '{{ $mitra->exists ? 'Ya, perbarui' : 'Ya, simpan' }}', cancelButtonText: 'Batal', reverseButtons: true,
            confirmButtonColor: '#1f8fff', cancelButtonColor: '#6d7a86',
            showClass: { popup: 'capolaga-swal-show' }, hideClass: { popup: 'capolaga-swal-hide' }
        }).then((result) => {
            if (!result.isConfirmed) return;
            isSubmitting = true; updateButtonState();
            Swal.fire({ title: '{{ $mitra->exists ? 'Memperbarui...' : 'Menyimpan...' }}', text: 'Mohon tunggu, data mitra sedang diproses.', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
            form.submit();
        });
    });

    updateButtonState();
});
</script>
@endpush
