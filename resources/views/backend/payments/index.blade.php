@extends('backend.main_backend')
@section('title', 'Manajemen Pembayaran')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Manajemen Pembayaran</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Pembayaran</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    {{-- Summary Cards --}}
    <div class="row mb-3">
        @php
        $cards = [
            'pending'  => ['label' => 'Pending',   'color' => 'warning'],
            'paid'     => ['label' => 'Lunas',      'color' => 'success'],
            'failed'   => ['label' => 'Gagal',      'color' => 'danger'],
            'expired'  => ['label' => 'Expired',    'color' => 'secondary'],
            'refunded' => ['label' => 'Refunded',   'color' => 'dark'],
        ];
        @endphp
        @foreach($cards as $key => $card)
        <div class="col-6 col-md-2">
            <a href="{{ route('admin.payments.index', ['status' => $key]) }}" class="text-decoration-none">
                <div class="small-box bg-{{ $card['color'] }}" style="min-height:80px">
                    <div class="inner py-2 px-3">
                        <h4 class="mb-0">{{ $statusCounts[$key] ?? 0 }}</h4>
                        <p class="mb-0" style="font-size:0.75rem">{{ $card['label'] }}</p>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
        <div class="col-6 col-md-2">
            <div class="small-box bg-primary" style="min-height:80px">
                <div class="inner py-2 px-3">
                    <h5 class="mb-0" style="font-size:0.9rem">Rp {{ number_format($totalPaid, 0, ',', '.') }}</h5>
                    <p class="mb-0" style="font-size:0.75rem">Total Lunas</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="d-flex align-items-center mb-2 flex-wrap gap-2">
        <form class="form-inline" method="GET">
            <div class="input-group input-group-sm mr-2 mb-1">
                <input type="text" name="q" class="form-control" placeholder="Kode / booking / nama / trx ID"
                    value="{{ request('q') }}" style="width:220px">
                <div class="input-group-append">
                    <button class="btn btn-default"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <select name="status" class="form-control form-control-sm mr-2 mb-1" style="width:130px">
                <option value="">Semua Status</option>
                @foreach(['pending','paid','failed','expired','refunded'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="method_type" class="form-control form-control-sm mr-2 mb-1" style="width:130px">
                <option value="">Semua Metode</option>
                @foreach(['va'=>'Virtual Account','ewallet'=>'E-Wallet','qris'=>'QRIS','cc'=>'Kartu Kredit','cstore'=>'Minimarket','manual'=>'Manual'] as $v => $l)
                    <option value="{{ $v }}" {{ request('method_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" class="form-control form-control-sm mr-1 mb-1" value="{{ request('date_from') }}">
            <input type="date" name="date_to" class="form-control form-control-sm mr-2 mb-1" value="{{ request('date_to') }}">
            <button type="submit" class="btn btn-sm btn-primary mr-1 mb-1">Filter</button>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-secondary mb-1">Reset</a>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Kode Bayar</th>
                        <th>Booking</th>
                        <th>Tamu</th>
                        <th>Metode</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-center">Status</th>
                        <th>Waktu</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                    @php
                        $pc = ['paid'=>'success','pending'=>'warning','failed'=>'danger','expired'=>'secondary','refunded'=>'dark'];
                        $typeIcons = ['va'=>'university','ewallet'=>'mobile-alt','qris'=>'qrcode','cc'=>'credit-card','cstore'=>'store','manual'=>'exchange-alt'];
                    @endphp
                    <tr>
                        <td><code class="text-xs">{{ $p->payment_code }}</code></td>
                        <td>
                            <a href="{{ route('admin.bookings.show', $p->booking_id) }}">
                                {{ $p->booking_code }}
                            </a>
                        </td>
                        <td>
                            <div>{{ $p->user_name }}</div>
                            <small class="text-muted">{{ $p->user_email }}</small>
                        </td>
                        <td>
                            <i class="fas fa-{{ $typeIcons[$p->method_type] ?? 'money-bill' }} mr-1 text-muted"></i>
                            {{ $p->method_name ?? '—' }}
                            @if($p->va_number)
                                <br><small class="text-muted">VA: {{ $p->va_number }}</small>
                            @endif
                        </td>
                        <td class="text-right">
                            <strong>Rp {{ number_format($p->amount, 0, ',', '.') }}</strong>
                            @if($p->fee_amount > 0)
                                <br><small class="text-muted">+Rp {{ number_format($p->fee_amount, 0, ',', '.') }} fee</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $pc[$p->status] ?? 'secondary' }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td>
                            <div>{{ \Carbon\Carbon::parse($p->created_at)->format('d M Y') }}</div>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($p->created_at)->format('H:i') }}</small>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.payments.show', $p->id) }}"
                                class="btn btn-xs btn-outline-info" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($p->status === 'pending')
                            <form action="{{ route('admin.payments.confirm', $p->id) }}" method="POST"
                                class="d-inline confirm-form">
                                @csrf
                                <button class="btn btn-xs btn-outline-success" title="Konfirmasi">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                            Belum ada data pembayaran.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
        <div class="card-footer">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('backend/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(function () {
    // Konfirmasi pembayaran
    $('.confirm-form').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Konfirmasi Pembayaran?',
            text: 'Status pembayaran akan diubah ke Lunas dan booking akan dikonfirmasi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Konfirmasi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745',
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
