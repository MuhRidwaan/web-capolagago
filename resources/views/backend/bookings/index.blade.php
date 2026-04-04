@extends('backend.main_backend')
@section('title', 'Manajemen Booking')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Manajemen Booking</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Booking</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    {{-- Status Summary Cards --}}
    <div class="row mb-3">
        @php
        $cards = [
            'pending'         => ['label' => 'Pending',       'color' => 'secondary', 'icon' => 'clock'],
            'waiting_payment' => ['label' => 'Menunggu Bayar','color' => 'warning',   'icon' => 'money-bill-wave'],
            'confirmed'       => ['label' => 'Terkonfirmasi', 'color' => 'info',      'icon' => 'check-circle'],
            'checked_in'      => ['label' => 'Check-In',      'color' => 'primary',   'icon' => 'sign-in-alt'],
            'completed'       => ['label' => 'Selesai',       'color' => 'success',   'icon' => 'flag-checkered'],
            'cancelled'       => ['label' => 'Dibatalkan',    'color' => 'danger',    'icon' => 'times-circle'],
        ];
        @endphp
        @foreach($cards as $key => $card)
        <div class="col-6 col-md-2">
            <a href="{{ route('admin.bookings.index', ['status' => $key]) }}" class="text-decoration-none">
                <div class="small-box bg-{{ $card['color'] }}" style="min-height:80px">
                    <div class="inner py-2 px-3">
                        <h4 class="mb-0">{{ $statusCounts[$key] ?? 0 }}</h4>
                        <p class="mb-0" style="font-size:0.75rem">{{ $card['label'] }}</p>
                    </div>
                    <div class="icon"><i class="fas fa-{{ $card['icon'] }}"></i></div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    {{-- Toolbar --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <form class="form-inline" method="GET">
            <div class="input-group input-group-sm mr-2">
                <input type="text" name="q" class="form-control" placeholder="Kode booking / nama / email"
                    value="{{ request('q') }}" style="width:220px">
                <div class="input-group-append">
                    <button class="btn btn-default"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <select name="status" class="form-control form-control-sm mr-2" style="width:160px">
                <option value="">Semua Status</option>
                @foreach(['pending','waiting_payment','confirmed','checked_in','completed','cancelled','refunded'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="date_from" class="form-control form-control-sm mr-1"
                value="{{ request('date_from') }}" title="Dari tanggal kunjungan">
            <input type="date" name="date_to" class="form-control form-control-sm mr-2"
                value="{{ request('date_to') }}" title="Sampai tanggal kunjungan">
            <button type="submit" class="btn btn-sm btn-primary mr-1">Filter</button>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-secondary">Reset</a>
        </form>
        <a href="{{ route('admin.bookings.create') }}" class="btn btn-sm btn-success">
            <i class="fas fa-plus mr-1"></i>Booking Manual
        </a>
    </div>

    {{-- Tabel --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Kode Booking</th>
                        <th>Tamu</th>
                        <th>Tgl Kunjungan</th>
                        <th class="text-center">Tamu</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Sumber</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Ubah Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
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
                        $statusFlow = [
                            'pending'         => ['waiting_payment','confirmed','cancelled'],
                            'waiting_payment' => ['confirmed','cancelled'],
                            'confirmed'       => ['checked_in','cancelled'],
                            'checked_in'      => ['completed'],
                            'completed'       => [],
                            'cancelled'       => ['refunded'],
                            'refunded'        => [],
                        ];
                        $nextStatuses = $statusFlow[$b->status] ?? [];
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.bookings.show', $b->id) }}" class="font-weight-bold">
                                {{ $b->booking_code }}
                            </a>
                        </td>
                        <td>
                            <div>{{ $b->user_name }}</div>
                            <small class="text-muted">{{ $b->user_email }}</small>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($b->visit_date)->format('d M Y') }}</td>
                        <td class="text-center">{{ $b->total_guests }}</td>
                        <td class="text-right">Rp {{ number_format($b->total_amount, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge badge-light border">{{ $b->source }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $statusColors[$b->status] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                            </span>
                        </td>
                        <td class="text-center" style="min-width:160px">
                            @if(count($nextStatuses) > 0)
                            <form action="{{ route('admin.bookings.update-status', $b->id) }}" method="POST"
                                class="form-inline justify-content-center status-form">
                                @csrf @method('PATCH')
                                <select name="status" class="form-control form-control-xs select2-status mr-1"
                                    style="width:120px; font-size:0.75rem">
                                    @foreach($nextStatuses as $ns)
                                        <option value="{{ $ns }}">{{ ucfirst(str_replace('_', ' ', $ns)) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-xs btn-outline-primary">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @else
                                <span class="text-muted text-xs">—</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.bookings.show', $b->id) }}"
                                class="btn btn-xs btn-outline-info" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(! in_array($b->status, ['completed','refunded']))
                            <a href="{{ route('admin.bookings.edit', $b->id) }}"
                                class="btn btn-xs btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                            Belum ada data booking.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
        <div class="card-footer">{{ $bookings->links() }}</div>
        @endif
    </div>
</div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('backend/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('backend/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(function () {
    // Select2 untuk dropdown status inline
    $('.select2-status').select2({
        theme: 'bootstrap4',
        minimumResultsForSearch: Infinity,
        width: '120px',
    });

    // Konfirmasi SweetAlert2 sebelum ubah status
    $('.status-form').on('submit', function (e) {
        e.preventDefault();
        const form   = this;
        const status = $(form).find('select[name=status]').val();
        const label  = $(form).find('select[name=status] option:selected').text();

        Swal.fire({
            title: 'Ubah Status?',
            text: `Status akan diubah ke "${label}"`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ubah',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745',
        }).then(result => {
            if (result.isConfirmed) form.submit();
        });
    });

    // Tampilkan SweetAlert2 dari flash session
    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 3000, showConfirmButton: false });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
    @endif
});
</script>
@endpush
