@extends('backend.main_backend')
@section('title', $method ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $method ? 'Edit Metode' : 'Tambah Metode' }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.payment-methods.index') }}">Metode Pembayaran</a></li>
                    <li class="breadcrumb-item active">{{ $method ? 'Edit' : 'Tambah' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    <form action="{{ $method ? route('admin.payment-methods.update', $method->id) : route('admin.payment-methods.store') }}"
        method="POST" enctype="multipart/form-data" id="payment-method-form" data-swal-managed="custom">
        @csrf
        @if($method) @method('PUT') @endif

        <div class="row">
            {{-- Kolom Kiri --}}
            <div class="col-lg-8">

                {{-- Identitas --}}
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-credit-card mr-2"></i>Identitas Metode</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama <span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $method?->name) }}"
                                        placeholder="BCA Virtual Account">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode <span class="text-danger">*</span></label>
                                    <input type="text" name="code"
                                        class="form-control text-uppercase @error('code') is-invalid @enderror"
                                        value="{{ old('code', $method?->code) }}"
                                        placeholder="BCA_VA" style="text-transform:uppercase">
                                    <small class="text-muted">Huruf kapital, tanpa spasi. Digunakan sebagai identifier internal.</small>
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe <span class="text-danger">*</span></label>
                                    <select name="type" class="form-control select2-basic @error('type') is-invalid @enderror">
                                        @foreach($types as $val => $label)
                                            <option value="{{ $val }}" {{ old('type', $method?->type) === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Provider <span class="text-danger">*</span></label>
                                    <select name="provider" class="form-control select2-basic @error('provider') is-invalid @enderror">
                                        @foreach($providers as $val => $label)
                                            <option value="{{ $val }}" {{ old('provider', $method?->provider ?? 'midtrans') === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('provider') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Logo --}}
                        <div class="form-group mb-0">
                            <label>Logo <small class="text-muted">(PNG/JPG/SVG/WebP, maks 512KB)</small></label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" name="logo" id="logo-input"
                                        class="custom-file-input @error('logo') is-invalid @enderror"
                                        accept="image/*">
                                    <label class="custom-file-label" for="logo-input">Pilih file...</label>
                                </div>
                            </div>
                            @if($method?->logo_path)
                            <div class="mt-2 d-flex align-items-center">
                                <img src="{{ Storage::url($method->logo_path) }}" alt="Logo"
                                    style="height:32px; object-fit:contain" class="mr-2 border p-1">
                                <small class="text-muted">Logo saat ini. Upload baru untuk mengganti.</small>
                            </div>
                            @endif
                            @error('logo') <div class="text-danger text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Biaya & Limit --}}
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-percentage mr-2"></i>Biaya & Limit Transaksi</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fee Flat (Rp)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="fee_flat"
                                            class="form-control @error('fee_flat') is-invalid @enderror"
                                            value="{{ old('fee_flat', $method?->fee_flat ?? 0) }}"
                                            min="0" step="500" placeholder="4000">
                                    </div>
                                    <small class="text-muted">Biaya tetap per transaksi. 0 = gratis.</small>
                                    @error('fee_flat') <div class="text-danger text-sm">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fee Persen (%)</label>
                                    <div class="input-group">
                                        <input type="number" name="fee_percent"
                                            class="form-control @error('fee_percent') is-invalid @enderror"
                                            value="{{ old('fee_percent', $method?->fee_percent ?? 0) }}"
                                            min="0" max="100" step="0.001" placeholder="2.000">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">Persentase dari total transaksi. 0 = gratis.</small>
                                    @error('fee_percent') <div class="text-danger text-sm">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label>Minimum Transaksi (Rp) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="min_amount"
                                            class="form-control @error('min_amount') is-invalid @enderror"
                                            value="{{ old('min_amount', $method?->min_amount ?? 10000) }}"
                                            min="0" step="1000">
                                    </div>
                                    @error('min_amount') <div class="text-danger text-sm">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label>Maksimum Transaksi (Rp)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="max_amount"
                                            class="form-control @error('max_amount') is-invalid @enderror"
                                            value="{{ old('max_amount', $method?->max_amount) }}"
                                            min="0" step="1000" placeholder="Kosongkan = tidak ada batas">
                                    </div>
                                    <small class="text-muted">Kosongkan jika tidak ada batas maksimum.</small>
                                    @error('max_amount') <div class="text-danger text-sm">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Kolom Kanan --}}
            <div class="col-lg-4">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cog mr-2"></i>Pengaturan Lain</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Urutan Tampil <span class="text-danger">*</span></label>
                            <input type="number" name="sort_order"
                                class="form-control @error('sort_order') is-invalid @enderror"
                                value="{{ old('sort_order', $method?->sort_order ?? 0) }}"
                                min="0" max="255">
                            <small class="text-muted">Angka kecil tampil lebih dulu. Bisa diubah via drag di halaman daftar.</small>
                            @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <div class="icheck-success">
                                <input type="checkbox" id="is_active" name="is_active"
                                    value="1" {{ old('is_active', $method?->is_active ?? true) ? 'checked' : '' }}>
                                <label for="is_active">Aktifkan metode ini</label>
                            </div>
                            <small class="text-muted">Metode nonaktif tidak akan muncul di halaman checkout.</small>
                        </div>
                    </div>
                </div>

                {{-- Preview fee --}}
                <div class="card card-outline card-warning" id="fee-preview">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calculator mr-2"></i>Preview Biaya</h3>
                    </div>
                    <div class="card-body" style="font-size:0.875rem">
                        <p class="text-muted mb-2">Contoh transaksi Rp 500.000:</p>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td>Fee Flat</td><td class="text-right" id="prev-flat">Rp 0</td></tr>
                            <tr><td>Fee %</td><td class="text-right" id="prev-pct">Rp 0</td></tr>
                            <tr class="font-weight-bold border-top">
                                <td>Total Fee</td><td class="text-right text-warning" id="prev-total">Rp 0</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary btn-block mb-2">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary btn-block" id="payment-method-submit-btn">
                            <i class="fas fa-save mr-1"></i>
                            {{ $method ? 'Simpan Perubahan' : 'Tambah Metode' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('backend/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('backend/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('backend/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>
$(function () {
    const form = document.getElementById('payment-method-form');
    const submitButton = document.getElementById('payment-method-submit-btn');
    let isSubmitting = false;

    bsCustomFileInput.init();

    $('.select2-basic').select2({ theme: 'bootstrap4', width: '100%' });

    // Auto uppercase kode
    $('input[name=code]').on('input', function () {
        this.value = this.value.toUpperCase().replace(/\s/g, '_');
    });

    // Preview fee
    function updatePreview() {
        const flat    = parseFloat($('input[name=fee_flat]').val()) || 0;
        const pct     = parseFloat($('input[name=fee_percent]').val()) || 0;
        const sample  = 500000;
        const pctFee  = sample * pct / 100;
        const total   = flat + pctFee;

        const fmt = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
        $('#prev-flat').text(fmt(flat));
        $('#prev-pct').text(fmt(pctFee));
        $('#prev-total').text(fmt(total));
    }

    $('input[name=fee_flat], input[name=fee_percent]').on('input', updatePreview);
    updatePreview();

    if (form && submitButton) {
        window.addEventListener('pageshow', function () {
            isSubmitting = false;
            if (typeof Swal !== 'undefined') Swal.close();
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
                form.submit();
                return;
            }

            Swal.fire({
                title: '{{ $method ? 'Update metode pembayaran?' : 'Tambah metode pembayaran?' }}',
                text: '{{ $method ? 'Perubahan metode pembayaran akan langsung disimpan.' : 'Metode pembayaran baru akan langsung ditambahkan.' }}',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '{{ $method ? 'Ya, update' : 'Ya, simpan' }}',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                confirmButtonColor: '#1f8fff',
                cancelButtonColor: '#6d7a86',
            }).then((result) => {
                if (!result.isConfirmed) return;

                isSubmitting = true;
                submitButton.disabled = true;

                Swal.fire({
                    title: '{{ $method ? 'Updating...' : 'Saving...' }}',
                    text: 'Mohon tunggu, data metode pembayaran sedang diproses.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading(),
                });

                form.submit();
            });
        });
    }

    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 3000, showConfirmButton: false });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
    @endif
});
</script>
@endpush
