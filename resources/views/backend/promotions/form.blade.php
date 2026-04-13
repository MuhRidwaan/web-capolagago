@extends('backend.main_backend')
@section('title', $pageTitle)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ $pageTitle }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.promotions.index') }}">Promo</a></li>
                    <li class="breadcrumb-item active">{{ $promo ? 'Edit' : 'Buat' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    <form action="{{ $promo ? route('admin.promotions.update', $promo->id) : route('admin.promotions.store') }}"
        method="POST">
        @csrf
        @if($promo) @method('PUT') @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-ticket-alt mr-2"></i>Detail Promo</h3>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Nama Promo <span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $promo?->name) }}"
                                        placeholder="Contoh: Diskon Lebaran 20%">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode Voucher <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="code" id="code"
                                            class="form-control text-uppercase @error('code') is-invalid @enderror"
                                            value="{{ old('code', $promo?->code) }}"
                                            placeholder="LEBARAN20"
                                            style="text-transform:uppercase">
                                        @if(!$promo)
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="btn-generate-code">
                                                <i class="fas fa-random"></i>
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                    @error('code')<div class="text-danger text-sm mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Keterangan singkat promo ini...">{{ old('description', $promo?->description) }}</textarea>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tipe Promo <span class="text-danger">*</span></label>
                                    <select name="promo_type_id" id="promo_type_id"
                                        class="form-control @error('promo_type_id') is-invalid @enderror">
                                        <option value="">-- Pilih Tipe --</option>
                                        @foreach($promoTypes as $t)
                                            <option value="{{ $t->id }}"
                                                data-type="{{ $t->discount_type }}"
                                                {{ old('promo_type_id', $promo?->promo_type_id) == $t->id ? 'selected' : '' }}>
                                                {{ $t->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('promo_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nilai Diskon <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="discount_value" id="discount_value"
                                            class="form-control @error('discount_value') is-invalid @enderror"
                                            value="{{ old('discount_value', $promo?->discount_value) }}"
                                            min="0" step="0.01">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="discount-unit">%</span>
                                        </div>
                                    </div>
                                    @error('discount_value')<div class="text-danger text-sm mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4" id="max-discount-wrap">
                                <div class="form-group">
                                    <label>Maks. Potongan <small class="text-muted">(untuk %)</small></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                        <input type="number" name="max_discount_amount"
                                            class="form-control @error('max_discount_amount') is-invalid @enderror"
                                            value="{{ old('max_discount_amount', $promo?->max_discount_amount) }}"
                                            min="0" placeholder="Kosongkan = tak terbatas">
                                    </div>
                                    @error('max_discount_amount')<div class="text-danger text-sm mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Min. Order</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                        <input type="number" name="min_order_amount"
                                            class="form-control @error('min_order_amount') is-invalid @enderror"
                                            value="{{ old('min_order_amount', $promo?->min_order_amount ?? 0) }}"
                                            min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kuota <small class="text-muted">(kosong = tak terbatas)</small></label>
                                    <input type="number" name="quota"
                                        class="form-control @error('quota') is-invalid @enderror"
                                        value="{{ old('quota', $promo?->quota) }}"
                                        min="1" placeholder="∞">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Maks. Pakai / User <span class="text-danger">*</span></label>
                                    <input type="number" name="max_use_per_user"
                                        class="form-control @error('max_use_per_user') is-invalid @enderror"
                                        value="{{ old('max_use_per_user', $promo?->max_use_per_user ?? 1) }}"
                                        min="1" max="255">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Berlaku Dari <span class="text-danger">*</span></label>
                                    <input type="date" name="valid_from"
                                        class="form-control @error('valid_from') is-invalid @enderror"
                                        value="{{ old('valid_from', $promo ? \Carbon\Carbon::parse($promo->valid_from)->format('Y-m-d') : '') }}">
                                    @error('valid_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Berlaku Sampai <span class="text-danger">*</span></label>
                                    <input type="date" name="valid_until"
                                        class="form-control @error('valid_until') is-invalid @enderror"
                                        value="{{ old('valid_until', $promo ? \Carbon\Carbon::parse($promo->valid_until)->format('Y-m-d') : '') }}">
                                    @error('valid_until')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <div class="icheck-primary">
                                <input type="checkbox" id="is_active" name="is_active" value="1"
                                    {{ old('is_active', $promo?->is_active ?? true) ? 'checked' : '' }}>
                                <label for="is_active">Promo aktif</label>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>{{ $promo ? 'Simpan Perubahan' : 'Buat Promo' }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                @if($promo)
                <div class="card card-outline card-info">
                    <div class="card-header"><h3 class="card-title">Statistik Penggunaan</h3></div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted">Digunakan</td><td><strong>{{ $promo->used_count }}x</strong></td></tr>
                            <tr><td class="text-muted">Kuota</td><td>{{ $promo->quota ?? '∞' }}</td></tr>
                            <tr><td class="text-muted">Sisa</td>
                                <td>{{ $promo->quota ? max(0, $promo->quota - $promo->used_count) : '∞' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @endif

                <div class="card card-outline card-secondary">
                    <div class="card-header"><h3 class="card-title">Panduan</h3></div>
                    <div class="card-body" style="font-size:0.85rem;line-height:1.8">
                        <p><strong>Kode Voucher</strong> — huruf kapital, angka, dan strip. Contoh: <code>LEBARAN20</code></p>
                        <p><strong>Diskon %</strong> — potongan persentase dari total. Isi "Maks. Potongan" untuk membatasi.</p>
                        <p><strong>Diskon Nominal</strong> — potongan langsung dalam Rupiah.</p>
                        <p class="mb-0"><strong>Kuota</strong> — kosongkan jika tidak ada batas pemakaian.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</section>
@endsection

@push('scripts')
<script>
$(function () {
    // Toggle unit diskon berdasarkan tipe
    function updateDiscountUnit() {
        const selected = $('#promo_type_id option:selected');
        const type = selected.data('type');
        if (type === 'percent') {
            $('#discount-unit').text('%');
            $('#max-discount-wrap').show();
        } else {
            $('#discount-unit').text('Rp');
            $('#max-discount-wrap').hide();
        }
    }

    $('#promo_type_id').on('change', updateDiscountUnit);
    updateDiscountUnit();

    // Generate kode random
    $('#btn-generate-code').on('click', function () {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        for (let i = 0; i < 8; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        $('#code').val(code);
    });

    // Auto uppercase kode
    $('#code').on('input', function () {
        this.value = this.value.toUpperCase();
    });
});
</script>
@endpush
