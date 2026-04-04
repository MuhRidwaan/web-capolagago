@extends('backend.main_backend')
@section('title', $booking ? 'Edit Booking' : 'Booking Manual')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ $booking ? 'Edit Booking' : 'Booking Manual' }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Booking</a></li>
                    <li class="breadcrumb-item active">{{ $booking ? 'Edit' : 'Baru' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    <form action="{{ $booking ? route('admin.bookings.update', $booking->id) : route('admin.bookings.store') }}"
        method="POST" id="booking-form">
        @csrf
        @if($booking) @method('PUT') @endif

        <div class="row">
            {{-- Kolom Kiri --}}
            <div class="col-lg-8">

                {{-- Data Tamu --}}
                @if(! $booking)
                <div class="card card-outline card-primary">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-user mr-2"></i>Data Tamu</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Tamu <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name"
                                        class="form-control @error('customer_name') is-invalid @enderror"
                                        value="{{ old('customer_name') }}" required>
                                    @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" name="customer_email"
                                        class="form-control @error('customer_email') is-invalid @enderror"
                                        value="{{ old('customer_email') }}" required>
                                    <small class="text-muted">Jika email sudah terdaftar, booking akan ditautkan ke akun tersebut.</small>
                                    @error('customer_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Detail Kunjungan --}}
                <div class="card card-outline card-info">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar mr-2"></i>Detail Kunjungan</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Kunjungan <span class="text-danger">*</span></label>
                                    <input type="date" name="visit_date"
                                        class="form-control @error('visit_date') is-invalid @enderror"
                                        value="{{ old('visit_date', $booking?->visit_date) }}"
                                        min="{{ today()->toDateString() }}" required>
                                    @error('visit_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Checkout <small class="text-muted">(opsional)</small></label>
                                    <input type="date" name="checkout_date"
                                        class="form-control @error('checkout_date') is-invalid @enderror"
                                        value="{{ old('checkout_date', $booking?->checkout_date) }}">
                                    <small class="text-muted">Isi untuk penginapan/glamping.</small>
                                    @error('checkout_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Jumlah Tamu <span class="text-danger">*</span></label>
                                    <input type="number" name="total_guests"
                                        class="form-control @error('total_guests') is-invalid @enderror"
                                        value="{{ old('total_guests', $booking?->total_guests ?? 1) }}"
                                        min="1" required>
                                    @error('total_guests') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sumber Booking <span class="text-danger">*</span></label>
                                    <select name="source" class="form-control select2-basic @error('source') is-invalid @enderror">
                                        @foreach(['web'=>'Web','mobile'=>'Mobile','whatsapp'=>'WhatsApp','walk_in'=>'Walk-in'] as $val => $label)
                                            <option value="{{ $val }}" {{ old('source', $booking?->source ?? 'walk_in') === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('source') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Promo <small class="text-muted">(opsional)</small></label>
                                    <input type="text" name="promo_code" class="form-control"
                                        value="{{ old('promo_code', $booking?->promo_code) }}"
                                        placeholder="CAPOLAGA10">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>Catatan Tamu</label>
                            <textarea name="notes" class="form-control" rows="2"
                                placeholder="Permintaan khusus, alergi, dll...">{{ old('notes', $booking?->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Item Produk (hanya saat create) --}}
                @if(! $booking)
                <div class="card card-outline card-success">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title"><i class="fas fa-box mr-2"></i>Produk / Layanan</h3>
                        <button type="button" class="btn btn-sm btn-success" id="btn-add-item">
                            <i class="fas fa-plus mr-1"></i>Tambah Item
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0" id="items-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Produk</th>
                                    <th width="100" class="text-center">Qty</th>
                                    <th width="140" class="text-right">Harga</th>
                                    <th width="140" class="text-right">Subtotal</th>
                                    <th width="40"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body">
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][product_id]" class="form-control form-control-sm select2-product" required>
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}" data-price="{{ $p->price }}"
                                                    data-label="{{ $p->price_label }}">
                                                    {{ $p->name }} — Rp {{ number_format($p->price, 0, ',', '.') }}{{ $p->price_label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]"
                                            class="form-control form-control-sm item-qty text-center"
                                            value="1" min="1" required>
                                    </td>
                                    <td class="text-right align-middle item-price text-muted">—</td>
                                    <td class="text-right align-middle item-subtotal font-weight-bold">—</td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-item">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td colspan="3" class="text-right font-weight-bold">Total</td>
                                    <td class="text-right font-weight-bold text-primary" id="grand-total">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @endif

            </div>

            {{-- Kolom Kanan --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <a href="{{ $booking ? route('admin.bookings.show', $booking->id) : route('admin.bookings.index') }}"
                            class="btn btn-secondary btn-block mb-2">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save mr-1"></i>
                            {{ $booking ? 'Simpan Perubahan' : 'Buat Booking' }}
                        </button>
                    </div>
                </div>
                @if(! $booking)
                <div class="card card-outline card-secondary">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Info</h3></div>
                    <div class="card-body" style="font-size:0.85rem">
                        <p class="mb-1">Booking manual dibuat dengan status <strong>Pending</strong>.</p>
                        <p class="mb-0 text-muted">Ubah status ke <em>Confirmed</em> setelah pembayaran diterima.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </form>
</div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('backend/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(function () {
    $('.select2-basic').select2({ theme: 'bootstrap4', width: '100%' });

    let itemIndex = 1;

    function formatRp(n) {
        return 'Rp ' + Math.round(n).toLocaleString('id-ID');
    }

    function initSelect2(row) {
        row.find('.select2-product').select2({ theme: 'bootstrap4', width: '100%' });
    }

    function recalcRow(row) {
        const price = parseFloat(row.find('.select2-product option:selected').data('price')) || 0;
        const qty   = parseInt(row.find('.item-qty').val()) || 0;
        row.find('.item-price').text(price ? formatRp(price) : '—');
        row.find('.item-subtotal').text(price ? formatRp(price * qty) : '—');
        recalcTotal();
    }

    function recalcTotal() {
        let total = 0;
        $('#items-body .item-row').each(function () {
            const price = parseFloat($(this).find('.select2-product option:selected').data('price')) || 0;
            const qty   = parseInt($(this).find('.item-qty').val()) || 0;
            total += price * qty;
        });
        $('#grand-total').text(formatRp(total));
    }

    // Init baris pertama
    initSelect2($('#items-body .item-row').first());

    $(document).on('change', '.select2-product', function () {
        recalcRow($(this).closest('.item-row'));
    });

    $(document).on('input', '.item-qty', function () {
        recalcRow($(this).closest('.item-row'));
    });

    // Tambah item
    $('#btn-add-item').on('click', function () {
        const template = $('#items-body .item-row').first().clone(false);
        template.find('select').val('').attr('name', `items[${itemIndex}][product_id]`);
        template.find('input').val(1).attr('name', `items[${itemIndex}][quantity]`);
        template.find('.item-price, .item-subtotal').text('—');
        $('#items-body').append(template);
        initSelect2(template);
        itemIndex++;
    });

    // Hapus item
    $(document).on('click', '.btn-remove-item', function () {
        if ($('#items-body .item-row').length <= 1) return;
        $(this).closest('.item-row').remove();
        recalcTotal();
    });
});
</script>
@endpush
