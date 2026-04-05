@extends('backend.main_backend')
@section('title', 'Detail Komisi')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Detail Komisi</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.commissions.index') }}">Komisi</a></li>
                    <li class="breadcrumb-item active">#{{ $commission->id }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    @php
        $sc = ['pending'=>'warning','processed'=>'info','settled'=>'success','cancelled'=>'danger'];
    @endphp

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-{{ $sc[$commission->status] ?? 'secondary' }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-percentage mr-2"></i>Komisi #{{ $commission->id }}
                    </h3>
                    <span class="badge badge-{{ $sc[$commission->status] ?? 'secondary' }} px-3 py-2">
                        {{ ucfirst($commission->status) }}
                    </span>
                </div>
                <div class="card-body">
                    {{-- Breakdown komisi --}}
                    <div class="row mb-4">
                        <div class="col-md-4 text-center border-right">
                            <div class="text-muted text-sm mb-1">Gross Amount</div>
                            <div class="font-weight-bold" style="font-size:1.2rem">
                                Rp {{ number_format($commission->gross_amount, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-md-4 text-center border-right">
                            <div class="text-muted text-sm mb-1">Komisi Platform ({{ $commission->commission_rate }}%)</div>
                            <div class="font-weight-bold text-danger" style="font-size:1.2rem">
                                Rp {{ number_format($commission->commission_amount, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="text-muted text-sm mb-1">Net ke Mitra</div>
                            <div class="font-weight-bold text-success" style="font-size:1.2rem">
                                Rp {{ number_format($commission->net_amount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted" width="140">Mitra</td>
                                    <td><strong>{{ $commission->business_name }}</strong></td></tr>
                                <tr><td class="text-muted">Bank</td>
                                    <td>{{ $commission->bank_name ?? '—' }}</td></tr>
                                <tr><td class="text-muted">No. Rekening</td>
                                    <td><code>{{ $commission->bank_account_no ?? '—' }}</code></td></tr>
                                <tr><td class="text-muted">Atas Nama</td>
                                    <td>{{ $commission->bank_account_name ?? '—' }}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted" width="140">Produk</td>
                                    <td>{{ $commission->product_name_snapshot }}</td></tr>
                                <tr><td class="text-muted">Qty</td>
                                    <td>{{ $commission->quantity }} × Rp {{ number_format($commission->unit_price, 0, ',', '.') }}</td></tr>
                                <tr><td class="text-muted">Booking</td>
                                    <td>
                                        <a href="{{ route('admin.bookings.show', $commission->booking_id) }}">
                                            {{ $commission->booking_code }}
                                        </a>
                                    </td></tr>
                                <tr><td class="text-muted">Tgl Kunjungan</td>
                                    <td>{{ \Carbon\Carbon::parse($commission->visit_date)->translatedFormat('d F Y') }}</td></tr>
                                <tr><td class="text-muted">Payment</td>
                                    <td><code>{{ $commission->payment_code }}</code></td></tr>
                                @if($commission->paid_at)
                                <tr><td class="text-muted">Dibayar</td>
                                    <td>{{ \Carbon\Carbon::parse($commission->paid_at)->format('d M Y H:i') }}</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($commission->settlement_ref)
                    <div class="alert alert-success mt-2 mb-0">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Settled:</strong>
                        Ref <code>{{ $commission->settlement_ref }}</code>
                        pada {{ \Carbon\Carbon::parse($commission->settled_at)->format('d M Y H:i') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Settle --}}
            @if(in_array($commission->status, ['pending','processed']))
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-check mr-2"></i>Settle Komisi</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted text-sm">Tandai komisi ini sudah dibayarkan ke mitra.</p>
                    <form action="{{ route('admin.commissions.settle', $commission->id) }}"
                        method="POST" id="form-settle">
                        @csrf
                        <div class="form-group">
                            <label>Referensi Transfer <span class="text-danger">*</span></label>
                            <input type="text" name="settlement_ref"
                                class="form-control @error('settlement_ref') is-invalid @enderror"
                                placeholder="TRF-20260405-001" required>
                            @error('settlement_ref')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-check mr-1"></i>Settle Sekarang
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Cancel --}}
            @if($commission->status !== 'settled' && $commission->status !== 'cancelled')
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-times mr-2"></i>Batalkan</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.commissions.cancel', $commission->id) }}"
                        method="POST" id="form-cancel">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-times mr-1"></i>Batalkan Komisi
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.commissions.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('backend/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(function () {
    $('#form-settle').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Settle komisi ini?',
            text: 'Pastikan transfer sudah dilakukan ke mitra.',
            icon: 'question', showCancelButton: true,
            confirmButtonText: 'Ya, Settle', cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });

    $('#form-cancel').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Batalkan komisi ini?', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Tidak', confirmButtonColor: '#dc3545',
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
