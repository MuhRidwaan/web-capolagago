@extends('backend.main_backend')
@section('title', 'Detail Booking #' . $booking->booking_code)

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Detail Booking</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Booking</a></li>
                    <li class="breadcrumb-item active">{{ $booking->booking_code }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    <div class="row">
        {{-- Kolom Kiri --}}
        <div class="col-lg-8">

            {{-- Info Booking --}}
            <div class="card card-outline card-primary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-ticket-alt mr-2"></i>
                        <strong>{{ $booking->booking_code }}</strong>
                    </h3>
                    @php
                    $statusColors = [
                        'pending'         => 'secondary',
                        'waiting_payment' => 'warning',
                        'confirmed'       => 'info',
                        'checked_in'      => 'primary',
                        'completed'       => 'success',
                        'cancelled'       => 'danger',
                        'refunded'        => 'dark',
                    ];
                    @endphp
                    <span class="badge badge-{{ $statusColors[$booking->status] ?? 'secondary' }} badge-lg px-3 py-2">
                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr><td class="text-muted" width="140">Tamu</td><td><strong>{{ $booking->user_name }}</strong></td></tr>
                                <tr><td class="text-muted">Email</td><td>{{ $booking->user_email }}</td></tr>
                                <tr><td class="text-muted">Jumlah Tamu</td><td>{{ $booking->total_guests }} orang</td></tr>
                                <tr><td class="text-muted">Sumber</td><td><span class="badge badge-light border">{{ $booking->source }}</span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr><td class="text-muted" width="140">Tgl Kunjungan</td><td><strong>{{ \Carbon\Carbon::parse($booking->visit_date)->translatedFormat('d F Y') }}</strong></td></tr>
                                @if($booking->checkout_date)
                                <tr><td class="text-muted">Checkout</td><td>{{ \Carbon\Carbon::parse($booking->checkout_date)->translatedFormat('d F Y') }}</td></tr>
                                @endif
                                <tr><td class="text-muted">Dibuat</td><td>{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y H:i') }}</td></tr>
                                @if($booking->promo_code)
                                <tr><td class="text-muted">Kode Promo</td><td><code>{{ $booking->promo_code }}</code></td></tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    @if($booking->notes)
                    <div class="alert alert-light border mt-3 mb-0">
                        <i class="fas fa-sticky-note mr-2 text-muted"></i>
                        <strong>Catatan:</strong> {{ $booking->notes }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Item Booking --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i>Item Pesanan</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Harga Satuan</th>
                                <th class="text-right">Subtotal</th>
                                <th class="text-center">Tipe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td>{{ $item->product_name_snapshot }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($item->is_addon)
                                        <span class="badge badge-info">Add-on</span>
                                    @else
                                        <span class="badge badge-success">Internal</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="3" class="text-right font-weight-bold">Subtotal</td>
                                <td class="text-right">Rp {{ number_format($booking->subtotal, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                            @if($booking->discount_amount > 0)
                            <tr>
                                <td colspan="3" class="text-right text-danger">Diskon</td>
                                <td class="text-right text-danger">- Rp {{ number_format($booking->discount_amount, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                            @endif
                            @if($booking->service_fee > 0)
                            <tr>
                                <td colspan="3" class="text-right text-muted">Biaya Layanan</td>
                                <td class="text-right text-muted">Rp {{ number_format($booking->service_fee, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="text-right font-weight-bold">Total</td>
                                <td class="text-right font-weight-bold text-primary">
                                    Rp {{ number_format($booking->total_amount, 0, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>

        {{-- Kolom Kanan --}}
        <div class="col-lg-4">

            {{-- Ubah Status --}}
            @if(count($allowedTransitions) > 0)
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i>Ubah Status</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.bookings.update-status', $booking->id) }}"
                        method="POST" id="form-status">
                        @csrf @method('PATCH')
                        <div class="form-group">
                            <label>Status Baru</label>
                            <select name="status" class="form-control select2-full" required>
                                @foreach($allowedTransitions as $s)
                                    <option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label>Catatan <small class="text-muted">(opsional)</small></label>
                            <textarea name="notes" class="form-control" rows="2"
                                placeholder="Alasan perubahan status..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <button type="submit" form="form-status" class="btn btn-warning btn-block" id="btn-status">
                        <i class="fas fa-save mr-1"></i>Simpan Status
                    </button>
                </div>
            </div>
            @endif

            {{-- Info Pembayaran --}}
            <div class="card card-outline card-{{ $payment && $payment->status === 'paid' ? 'success' : 'secondary' }}">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-bill-wave mr-2"></i>Pembayaran</h3>
                </div>
                <div class="card-body">
                    @if($payment)
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">Kode</td><td><code>{{ $payment->payment_code }}</code></td></tr>
                        <tr><td class="text-muted">Metode</td><td>{{ $payment->method_name ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Jumlah</td><td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td></tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @php $pc = ['paid'=>'success','pending'=>'warning','failed'=>'danger','expired'=>'secondary','refunded'=>'dark']; @endphp
                                <span class="badge badge-{{ $pc[$payment->status] ?? 'secondary' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                        @if($payment->paid_at)
                        <tr><td class="text-muted">Dibayar</td><td>{{ \Carbon\Carbon::parse($payment->paid_at)->format('d M Y H:i') }}</td></tr>
                        @endif
                        @if($payment->va_number)
                        <tr><td class="text-muted">No. VA</td><td><code>{{ $payment->va_number }}</code></td></tr>
                        @endif
                    </table>
                    <div class="mt-3">
                        <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-sm btn-outline-info btn-block">
                            <i class="fas fa-eye mr-1"></i>Lihat Detail Pembayaran
                        </a>
                    </div>
                    @else
                    <p class="text-muted text-center mb-0">Belum ada data pembayaran.</p>
                    @endif
                </div>
            </div>

            {{-- Aksi --}}
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-block mb-2">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
                    </a>
                    @if(! in_array($booking->status, ['completed','refunded']))
                    <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit mr-1"></i>Edit Booking
                    </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('backend/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('backend/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(function () {
    $('.select2-full').select2({ theme: 'bootstrap4', width: '100%' });

    $('#form-status').on('submit', function (e) {
        e.preventDefault();
        const form  = this;
        const label = $(form).find('select[name=status] option:selected').text();
        Swal.fire({
            title: 'Ubah Status?',
            text: `Status akan diubah ke "${label}"`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ubah',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ffc107',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });

    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 3000, showConfirmButton: false });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
    @endif
});
</script>
@endpush
