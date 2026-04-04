@extends('backend.main_backend')
@section('title', 'Detail Pembayaran')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Detail Pembayaran</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Pembayaran</a></li>
                    <li class="breadcrumb-item active">{{ $payment->payment_code }}</li>
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
            <div class="card card-outline card-{{ $payment->status === 'paid' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-receipt mr-2"></i>
                        <code>{{ $payment->payment_code }}</code>
                    </h3>
                    @php $pc = ['paid'=>'success','pending'=>'warning','failed'=>'danger','expired'=>'secondary','refunded'=>'dark']; @endphp
                    <span class="badge badge-{{ $pc[$payment->status] ?? 'secondary' }} px-3 py-2">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted" width="160">Kode Booking</td>
                                    <td><a href="{{ route('admin.bookings.show', $payment->booking_id) }}">{{ $payment->booking_code }}</a></td></tr>
                                <tr><td class="text-muted">Tamu</td><td><strong>{{ $payment->user_name }}</strong></td></tr>
                                <tr><td class="text-muted">Email</td><td>{{ $payment->user_email }}</td></tr>
                                <tr><td class="text-muted">Tgl Kunjungan</td>
                                    <td>{{ \Carbon\Carbon::parse($payment->visit_date)->translatedFormat('d F Y') }}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted" width="160">Metode</td><td>{{ $payment->method_name ?? '—' }}</td></tr>
                                <tr><td class="text-muted">Provider</td>
                                    <td><span class="badge badge-light border text-uppercase">{{ $payment->provider ?? '—' }}</span></td></tr>
                                <tr><td class="text-muted">Jumlah</td>
                                    <td><strong class="text-primary">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td></tr>
                                @if($payment->fee_amount > 0)
                                <tr><td class="text-muted">Biaya Gateway</td>
                                    <td>Rp {{ number_format($payment->fee_amount, 0, ',', '.') }}</td></tr>
                                @endif
                                <tr><td class="text-muted">Dibuat</td>
                                    <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i') }}</td></tr>
                                @if($payment->paid_at)
                                <tr><td class="text-muted">Dibayar</td>
                                    <td class="text-success">{{ \Carbon\Carbon::parse($payment->paid_at)->format('d M Y H:i') }}</td></tr>
                                @endif
                                @if($payment->expired_at)
                                <tr><td class="text-muted">Expired</td>
                                    <td class="text-danger">{{ \Carbon\Carbon::parse($payment->expired_at)->format('d M Y H:i') }}</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- VA / QR --}}
                    @if($payment->va_number)
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-university mr-2"></i>
                        <strong>Nomor Virtual Account:</strong>
                        <code class="ml-2 font-size-lg">{{ $payment->va_number }}</code>
                    </div>
                    @endif

                    @if($payment->qr_url)
                    <div class="text-center mb-3">
                        <p class="text-muted mb-2">QR Code Pembayaran</p>
                        <img src="{{ $payment->qr_url }}" alt="QR Code" style="max-width:200px" class="border p-2">
                    </div>
                    @endif

                    {{-- Gateway Info --}}
                    @if($payment->gateway_transaction_id)
                    <div class="alert alert-light border">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Transaction ID</small>
                                <code>{{ $payment->gateway_transaction_id }}</code>
                            </div>
                            @if($payment->gateway_order_id)
                            <div class="col-md-6">
                                <small class="text-muted d-block">Order ID</small>
                                <code>{{ $payment->gateway_order_id }}</code>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Raw Gateway Response --}}
                    @if($payment->gateway_response)
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-secondary" type="button"
                            data-toggle="collapse" data-target="#raw-response">
                            <i class="fas fa-code mr-1"></i>Raw Gateway Response
                        </button>
                        <div class="collapse mt-2" id="raw-response">
                            <pre class="bg-dark text-light p-3 rounded" style="font-size:0.75rem; max-height:300px; overflow-y:auto">{{ json_encode(json_decode($payment->gateway_response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Kanan — Aksi --}}
        <div class="col-lg-4">

            {{-- Konfirmasi Manual --}}
            @if($payment->status === 'pending')
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-check-circle mr-2"></i>Konfirmasi Manual</h3></div>
                <div class="card-body">
                    <p class="text-muted text-sm">Gunakan ini untuk konfirmasi pembayaran transfer bank yang sudah masuk.</p>
                    <form action="{{ route('admin.payments.confirm', $payment->id) }}" method="POST" id="form-confirm">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-check mr-1"></i>Konfirmasi Pembayaran
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Refund --}}
            @if($payment->status === 'paid')
            <div class="card card-outline card-danger">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-undo mr-2"></i>Proses Refund</h3></div>
                <div class="card-body">
                    <form action="{{ route('admin.payments.refund', $payment->id) }}" method="POST" id="form-refund">
                        @csrf
                        <div class="form-group">
                            <label>Alasan Refund <span class="text-danger">*</span></label>
                            <textarea name="refund_reason" class="form-control @error('refund_reason') is-invalid @enderror"
                                rows="3" placeholder="Jelaskan alasan refund..." required>{{ old('refund_reason') }}</textarea>
                            @error('refund_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-undo mr-1"></i>Proses Refund
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Cek Gateway --}}
            @if($payment->gateway_transaction_id)
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-sync mr-2"></i>Cek Gateway</h3></div>
                <div class="card-body">
                    <p class="text-muted text-sm">Cek status transaksi langsung ke Midtrans.</p>
                    <a href="{{ route('admin.payments.check-gateway', $payment->id) }}"
                        class="btn btn-info btn-block">
                        <i class="fas fa-sync mr-1"></i>Cek Status Midtrans
                    </a>
                </div>
            </div>
            @endif

            {{-- Navigasi --}}
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary btn-block mb-2">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
                    </a>
                    <a href="{{ route('admin.bookings.show', $payment->booking_id) }}" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-ticket-alt mr-1"></i>Lihat Booking
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
    $('#form-confirm').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Konfirmasi Pembayaran?',
            text: 'Status akan diubah ke Lunas dan booking akan dikonfirmasi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Konfirmasi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });

    $('#form-refund').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Proses Refund?',
            text: 'Tindakan ini tidak bisa dibatalkan. Status pembayaran akan diubah ke Refunded.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Refund',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
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
