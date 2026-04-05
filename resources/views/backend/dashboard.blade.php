@extends('backend.main_backend')

@section('title', 'Dashboard')

@push('styles')
<style>
    .capolaga-dashboard-hero {
        position: relative;
        overflow: hidden;
        border: 0;
        border-radius: 1rem;
        background: linear-gradient(135deg, #0b5e4a 0%, #16795f 55%, #c7efe2 160%);
        box-shadow: 0 18px 40px rgba(11, 94, 74, 0.18);
        color: #fff;
    }

    .capolaga-dashboard-hero::after {
        content: '';
        position: absolute;
        inset: auto -4rem -5rem auto;
        width: 18rem;
        height: 18rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
    }

    .capolaga-dashboard-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        color: #f7fffc;
        font-weight: 600;
    }

    .capolaga-dashboard-stat {
        border: 0;
        border-radius: 0.95rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .capolaga-dashboard-stat .card-body {
        padding: 1.15rem 1.2rem;
    }

    .capolaga-dashboard-stat-value {
        margin: 0.15rem 0 0.2rem;
        font-size: 1.7rem;
        font-weight: 700;
        line-height: 1.1;
        color: #12263a;
    }

    .capolaga-dashboard-stat-label {
        margin: 0;
        font-size: 0.86rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #587085;
    }

    .capolaga-dashboard-stat-meta {
        margin: 0;
        color: #74879b;
        font-size: 0.9rem;
    }

    .capolaga-dashboard-stat-bar {
        height: 0.35rem;
    }

    .capolaga-dashboard-panel {
        border: 0;
        border-radius: 0.95rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    .capolaga-dashboard-panel .card-header {
        background: #ffffff;
        border-bottom: 1px solid #edf2f7;
        padding: 1rem 1.15rem 0.85rem;
    }

    .capolaga-dashboard-panel .card-title {
        color: #163247;
        font-weight: 700;
    }

    .capolaga-status-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .capolaga-status-box {
        border-radius: 0.85rem;
        padding: 0.95rem 1rem;
        background: #f8fbfd;
        border: 1px solid #e6eef4;
    }

    .capolaga-status-box strong {
        display: block;
        font-size: 1.35rem;
        color: #10283d;
    }

    .capolaga-mini-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .capolaga-mini-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px dashed #dbe5ec;
    }

    .capolaga-mini-item:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .capolaga-table-compact th,
    .capolaga-table-compact td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
    }

    .capolaga-empty-state {
        padding: 2rem 1rem;
        text-align: center;
        color: #75879b;
    }

    @media (max-width: 767.98px) {
        .capolaga-status-grid {
            grid-template-columns: 1fr;
        }

        .capolaga-dashboard-stat-value {
            font-size: 1.45rem;
        }
    }
</style>
@endpush

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card capolaga-dashboard-hero mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="text-uppercase small font-weight-bold mb-2" style="letter-spacing:.08em; opacity:.8;">
                                {{ $hero['eyebrow'] }}
                            </div>
                            <h2 class="mb-3 font-weight-bold" style="font-size:2.1rem; line-height:1.2;">
                                {{ $hero['title'] }}
                            </h2>
                            <p class="mb-4" style="max-width:52rem; color:rgba(255,255,255,.88); font-size:1rem;">
                                {{ $hero['subtitle'] }}
                            </p>
                            <div class="d-flex flex-wrap" style="gap:.65rem;">
                                <span class="capolaga-dashboard-chip"><i class="far fa-calendar-alt"></i> {{ $todayLabel }}</span>
                                <span class="capolaga-dashboard-chip"><i class="fas fa-box-open"></i> {{ number_format((float) $totalProducts, 0, ',', '.') }} produk</span>
                                <span class="capolaga-dashboard-chip"><i class="fas fa-star"></i> {{ number_format((float) $averageRating, 2, ',', '.') }} rating rata-rata</span>
                                <span class="capolaga-dashboard-chip"><i class="fas fa-comment-dots"></i> {{ number_format((float) $totalReviews, 0, ',', '.') }} ulasan</span>
                            </div>
                        </div>
                        <div class="col-lg-4 mt-4 mt-lg-0">
                            <div class="p-4 rounded-lg" style="background:rgba(255,255,255,.12); backdrop-filter: blur(8px);">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="font-weight-bold">Slot Hari Ini</span>
                                    <span class="badge badge-light">{{ number_format((float) ($slotsToday->total_slots ?? 0), 0, ',', '.') }} slot</span>
                                </div>
                                <div class="mb-3">
                                    <div class="small text-uppercase font-weight-bold" style="opacity:.75;">Sisa Kapasitas</div>
                                    <div class="h3 mb-0 font-weight-bold">{{ number_format((float) ($slotsToday->remaining_capacity ?? 0), 0, ',', '.') }}</div>
                                </div>
                                <div class="d-flex justify-content-between" style="color:rgba(255,255,255,.86);">
                                    <div>
                                        <div class="small text-uppercase font-weight-bold" style="opacity:.75;">Blocked</div>
                                        <div class="font-weight-bold">{{ number_format((float) ($slotsToday->blocked_slots ?? 0), 0, ',', '.') }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="small text-uppercase font-weight-bold" style="opacity:.75;">Scope</div>
                                        <div class="font-weight-bold">{{ $isMitra ? 'Produk Mitra' : 'Semua Produk' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                @foreach ($stats as $stat)
                    <div class="col-md-6 col-xl-3">
                        <div class="card capolaga-dashboard-stat mb-4">
                            <div class="capolaga-dashboard-stat-bar bg-{{ $stat['tone'] }}"></div>
                            <div class="card-body">
                                <p class="capolaga-dashboard-stat-label">{{ $stat['label'] }}</p>
                                <div class="capolaga-dashboard-stat-value">{{ $stat['value'] }}</div>
                                <p class="capolaga-dashboard-stat-meta">{{ $stat['meta'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @php
                $statusCards = [
                    'pending' => ['label' => 'Pending', 'tone' => 'secondary'],
                    'waiting_payment' => ['label' => 'Menunggu Bayar', 'tone' => 'warning'],
                    'confirmed' => ['label' => 'Terkonfirmasi', 'tone' => 'success'],
                    'checked_in' => ['label' => 'Check In', 'tone' => 'info'],
                    'completed' => ['label' => 'Selesai', 'tone' => 'primary'],
                    'cancelled' => ['label' => 'Dibatalkan', 'tone' => 'danger'],
                ];
            @endphp

            <div class="row">
                <div class="col-lg-5">
                    <div class="card capolaga-dashboard-panel mb-4">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Status Operasional Booking</h3>
                        </div>
                        <div class="card-body">
                            <div class="capolaga-status-grid">
                                @foreach ($statusCards as $key => $statusCard)
                                    <div class="capolaga-status-box">
                                        <div class="small text-uppercase font-weight-bold text-{{ $statusCard['tone'] }}">{{ $statusCard['label'] }}</div>
                                        <strong>{{ number_format((float) ($bookingStatusCounts[$key] ?? 0), 0, ',', '.') }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card capolaga-dashboard-panel mb-4">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Sumber Booking</h3>
                        </div>
                        <div class="card-body">
                            <div class="capolaga-mini-list">
                                @forelse ($sourceBreakdown as $source)
                                    <div class="capolaga-mini-item">
                                        <div>
                                            <div class="font-weight-bold">{{ str($source->source)->headline() }}</div>
                                            <div class="text-muted small">Distribusi channel pemesanan</div>
                                        </div>
                                        <span class="badge badge-light border px-3 py-2">{{ number_format((float) $source->total, 0, ',', '.') }} booking</span>
                                    </div>
                                @empty
                                    <div class="capolaga-empty-state">
                                        Belum ada data sumber booking untuk ditampilkan.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card capolaga-dashboard-panel mb-4">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Produk Paling Laris Bulan Ini</h3>
                        </div>
                        <div class="card-body">
                            <div class="capolaga-mini-list">
                                @forelse ($topProducts as $product)
                                    <div class="capolaga-mini-item">
                                        <div>
                                            <div class="font-weight-bold">{{ $product->product_name }}</div>
                                            <div class="text-muted small">{{ $product->category_name }} • {{ number_format((float) $product->total_qty, 0, ',', '.') }} qty</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-weight-bold text-success">Rp {{ number_format((float) $product->total_revenue, 0, ',', '.') }}</div>
                                            <div class="text-muted small">Revenue</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="capolaga-empty-state">
                                        Belum ada performa produk yang tercatat pada bulan ini.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card capolaga-dashboard-panel mb-4">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Kunjungan Terdekat</h3>
                        </div>
                        <div class="card-body">
                            <div class="capolaga-mini-list">
                                @forelse ($upcomingVisits as $visit)
                                    <div class="capolaga-mini-item">
                                        <div>
                                            <div class="font-weight-bold">{{ $visit->booking_code }}</div>
                                            <div class="text-muted small">{{ $visit->customer_name }} • {{ number_format((float) $visit->total_guests, 0, ',', '.') }} tamu</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-weight-bold">{{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}</div>
                                            <span class="badge badge-{{ in_array($visit->status, ['confirmed', 'checked_in']) ? 'success' : 'warning' }}">{{ str($visit->status)->headline() }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="capolaga-empty-state">
                                        Belum ada kunjungan terjadwal dalam waktu dekat.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <div class="card capolaga-dashboard-panel mb-4">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Booking Terbaru</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0 capolaga-table-compact">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Pelanggan</th>
                                            <th>Tanggal Kunjungan</th>
                                            <th class="text-right">Nilai</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentBookings as $booking)
                                            <tr>
                                                <td class="font-weight-bold">{{ $booking->booking_code }}</td>
                                                <td>{{ $booking->customer_name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($booking->visit_date)->format('d M Y') }}</td>
                                                <td class="text-right">Rp {{ number_format((float) ($booking->total_amount ?? 0), 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-{{ in_array($booking->status, ['confirmed', 'completed']) ? 'success' : (in_array($booking->status, ['waiting_payment']) ? 'warning' : 'secondary') }}">
                                                        {{ str($booking->status)->headline() }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="capolaga-empty-state">Belum ada booking terbaru untuk ditampilkan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card capolaga-dashboard-panel mb-4">
                        <div class="card-header">
                            <h3 class="card-title mb-0">{{ $isMitra ? 'Aktivitas Penjualan Produk' : 'Aktivitas Pembayaran Terbaru' }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="capolaga-mini-list">
                                @forelse ($recentActivity as $activity)
                                    <div class="capolaga-mini-item">
                                        @if ($isMitra)
                                            <div>
                                                <div class="font-weight-bold">{{ $activity->product_name }}</div>
                                                <div class="text-muted small">{{ $activity->customer_name }} • {{ $activity->booking_code }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-weight-bold text-success">Rp {{ number_format((float) $activity->subtotal, 0, ',', '.') }}</div>
                                                <div class="text-muted small">{{ number_format((float) $activity->quantity, 0, ',', '.') }} qty • {{ \Carbon\Carbon::parse($activity->visit_date)->format('d M') }}</div>
                                            </div>
                                        @else
                                            <div>
                                                <div class="font-weight-bold">{{ $activity->payment_code }}</div>
                                                <div class="text-muted small">{{ $activity->booking_code }} • {{ $activity->payment_method_name ?? 'Metode belum dipilih' }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-weight-bold">Rp {{ number_format((float) $activity->amount, 0, ',', '.') }}</div>
                                                <div class="text-muted small">{{ str($activity->status)->headline() }} • {{ \Carbon\Carbon::parse($activity->created_at)->format('d M H:i') }}</div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="capolaga-empty-state">
                                        Belum ada aktivitas terbaru untuk ditampilkan.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
