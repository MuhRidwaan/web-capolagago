@extends('backend.main_backend')
@section('title', 'Manajemen Booking')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    .booking-summary-card .small-box {
        min-height: 96px;
        border-radius: 12px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        transition: transform .18s ease, box-shadow .18s ease;
        overflow: hidden;
    }

    .booking-summary-card .small-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12);
    }

    .booking-summary-card .small-box .inner {
        position: relative;
        z-index: 2;
    }

    .booking-summary-card .small-box .icon {
        top: 12px;
        right: 12px;
        opacity: .32;
        font-size: 60px;
    }

    .booking-toolbar-card,
    .booking-table-card {
        border: 0;
        border-radius: 14px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .booking-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .booking-filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        align-items: flex-end;
    }

    .booking-filter-group {
        min-width: 150px;
    }

    .booking-filter-search {
        min-width: 300px;
    }

    .booking-filter-label {
        display: block;
        margin-bottom: .4rem;
        font-size: .75rem;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .booking-table {
        margin-bottom: 0;
    }

    .booking-table thead th {
        border-top: 0;
        background: #f8fafc;
        font-size: .77rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #5f6c7b;
        white-space: nowrap;
    }

    .booking-table tbody td {
        vertical-align: middle;
        padding-top: .95rem;
        padding-bottom: .95rem;
    }

    .booking-table tbody tr:hover {
        background: #fbfdff;
    }

    .booking-code-link {
        font-weight: 700;
    }

    .booking-meta {
        color: #6b7280;
        font-size: .82rem;
    }

    .booking-empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: #6b7280;
    }

    .booking-empty-state i {
        font-size: 2.2rem;
        margin-bottom: .75rem;
        color: #94a3b8;
    }

    .booking-pagination {
        padding: 1rem 1.25rem;
        background: #fff;
        border-top: 1px solid #eef2f7;
    }

    @media (max-width: 767.98px) {
        .booking-filter-search,
        .booking-filter-group {
            min-width: 100%;
        }

        .booking-filter-form .btn {
            width: 100%;
        }
    }
</style>
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

    @php
        $cards = [
            'pending' => ['label' => 'Pending', 'color' => 'secondary', 'icon' => 'clock'],
            'waiting_payment' => ['label' => 'Menunggu Bayar', 'color' => 'warning', 'icon' => 'money-bill-wave'],
            'confirmed' => ['label' => 'Terkonfirmasi', 'color' => 'info', 'icon' => 'check-circle'],
            'checked_in' => ['label' => 'Check-In', 'color' => 'primary', 'icon' => 'sign-in-alt'],
            'completed' => ['label' => 'Selesai', 'color' => 'success', 'icon' => 'flag-checkered'],
            'cancelled' => ['label' => 'Dibatalkan', 'color' => 'danger', 'icon' => 'times-circle'],
        ];

        $statusColors = [
            'pending' => 'secondary',
            'waiting_payment' => 'warning',
            'confirmed' => 'info',
            'checked_in' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'dark',
        ];

        $statusFlow = [
            'pending' => ['waiting_payment', 'confirmed', 'cancelled'],
            'waiting_payment' => ['confirmed', 'cancelled'],
            'confirmed' => ['checked_in', 'cancelled'],
            'checked_in' => ['completed'],
            'completed' => [],
            'cancelled' => ['refunded'],
            'refunded' => [],
        ];

        $sourceLabels = [
            'web' => 'Website',
            'mobile' => 'Mobile App',
            'whatsapp' => 'WhatsApp',
            'walk_in' => 'Walk In',
        ];
    @endphp

    <div class="row mb-3">
        @foreach($cards as $key => $card)
        <div class="col-6 col-md-4 col-xl-2 booking-summary-card mb-3">
            <a href="{{ route('admin.bookings.index', ['status' => $key]) }}" class="text-decoration-none">
                <div class="small-box bg-{{ $card['color'] }}">
                    <div class="inner py-3 px-3">
                        <h4 class="mb-1">{{ $statusCounts[$key] ?? 0 }}</h4>
                        <p class="mb-0">{{ $card['label'] }}</p>
                    </div>
                    <div class="icon"><i class="fas fa-{{ $card['icon'] }}"></i></div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <div class="card booking-toolbar-card mb-3">
        <div class="card-body">
            <div class="booking-toolbar">
                <form class="booking-filter-form" method="GET">
                    <div class="booking-filter-search">
                        <label class="booking-filter-label">Pencarian</label>
                        <div class="input-group">
                            <input type="text" name="q" class="form-control" placeholder="Kode booking / nama / email"
                                value="{{ request('q') }}">
                            <div class="input-group-append">
                                <button class="btn btn-default" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="booking-filter-group">
                        <label class="booking-filter-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            @foreach(['pending','waiting_payment','confirmed','checked_in','completed','cancelled','refunded'] as $s)
                                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $s)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="booking-filter-group">
                        <label class="booking-filter-label">Dari</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="booking-filter-group">
                        <label class="booking-filter-label">Sampai</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter mr-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Reset</a>
                </form>

                <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i>Booking Manual
                </a>
            </div>
        </div>
    </div>

    <div class="card booking-table-card">
        <div class="card-header bg-white border-0 pb-0">
            <h3 class="card-title font-weight-bold">Daftar Booking</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover booking-table mb-0">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Tamu</th>
                            <th>Tgl Kunjungan</th>
                            <th class="text-center">Jumlah Tamu</th>
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
                            $nextStatuses = $statusFlow[$b->status] ?? [];
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.bookings.show', $b->id) }}" class="booking-code-link">
                                    {{ $b->booking_code }}
                                </a>
                                <div class="booking-meta">
                                    Dibuat {{ \Carbon\Carbon::parse($b->created_at)->format('d M Y H:i') }}
                                </div>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $b->user_name }}</div>
                                <small class="booking-meta">{{ $b->user_email }}</small>
                            </td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($b->visit_date)->format('d M Y') }}</div>
                                @if($b->checkout_date)
                                <small class="booking-meta">Checkout {{ \Carbon\Carbon::parse($b->checkout_date)->format('d M Y') }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ $b->total_guests }}</td>
                            <td class="text-right">Rp {{ number_format($b->total_amount, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge badge-light border px-3 py-2">
                                    {{ $sourceLabels[$b->source] ?? $b->source }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $statusColors[$b->status] ?? 'secondary' }} px-3 py-2">
                                    {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                                </span>
                            </td>
                            <td class="text-center" style="min-width:170px">
                                @if(count($nextStatuses) > 0)
                                <form action="{{ route('admin.bookings.update-status', $b->id) }}" method="POST"
                                    class="form-inline justify-content-center status-form">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-control form-control-sm select2-status mr-1" style="width:125px;">
                                        @foreach($nextStatuses as $ns)
                                            <option value="{{ $ns }}">{{ ucfirst(str_replace('_', ' ', $ns)) }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @else
                                    <span class="booking-meta">-</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.bookings.show', $b->id) }}"
                                    class="btn btn-sm btn-outline-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(! in_array($b->status, ['completed','refunded']))
                                <a href="{{ route('admin.bookings.edit', $b->id) }}"
                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9">
                                <div class="booking-empty-state">
                                    <i class="fas fa-calendar-times d-block"></i>
                                    <div class="font-weight-bold mb-1">Belum ada data booking.</div>
                                    <div>Coba ubah filter atau tambahkan booking manual baru.</div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($bookings->hasPages())
        <div class="booking-pagination">
            {{ $bookings->links() }}
        </div>
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
    $('.select2-status').select2({
        theme: 'bootstrap4',
        minimumResultsForSearch: Infinity,
        width: '125px'
    });

    $('.status-form').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const label = $(form).find('select[name=status] option:selected').text();

        Swal.fire({
            title: 'Ubah Status?',
            text: `Status akan diubah ke "${label}"`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ubah',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745'
        }).then(result => {
            if (result.isConfirmed) form.submit();
        });
    });

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: @json(session('success')),
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: @json(session('error'))
        });
    @endif
});
</script>
@endpush
