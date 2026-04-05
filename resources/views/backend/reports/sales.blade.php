@extends('backend.main_backend')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Laporan Penjualan</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Laporan Penjualan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('backend.layouts.flash')

        <div class="card">
            <div class="card-header"><h3 class="card-title mb-0">Filter Laporan</h3></div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.index') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal Dari</label>
                                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal Sampai</label>
                                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status Pembayaran</label>
                                <select name="status" class="form-control">
                                    <option value="">Semua Status</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->headline() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipe Metode</label>
                                <select name="method_type" class="form-control">
                                    <option value="">Semua Metode</option>
                                    @foreach ($methodTypes as $methodType => $label)
                                        <option value="{{ $methodType }}" @selected(request('method_type') === $methodType)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap" style="gap:.5rem;">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format((float) ($summary->total_transactions ?? 0), 0, ',', '.') }}</h3>
                        <p>Total Transaksi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>Rp {{ number_format((float) ($summary->total_paid ?? 0), 0, ',', '.') }}</h3>
                        <p>Penjualan Dibayar</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>Rp {{ number_format((float) ($summary->total_refunded ?? 0), 0, ',', '.') }}</h3>
                        <p>Total Refund</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>Rp {{ number_format((float) ($summary->average_ticket ?? 0), 0, ',', '.') }}</h3>
                        <p>Rata-rata Ticket</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title mb-0">Ringkasan Harian</h3></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th class="text-right">Transaksi</th>
                                        <th class="text-right">Dibayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dailySales as $day)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($day->report_date)->format('d M Y') }}</td>
                                            <td class="text-right">{{ number_format((float) $day->transactions_count, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format((float) $day->paid_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Belum ada data penjualan pada periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h3 class="card-title mb-0">Detail Transaksi</h3></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Kode Bayar</th>
                                        <th>Booking</th>
                                        <th>Pelanggan</th>
                                        <th>Metode</th>
                                        <th class="text-right">Amount</th>
                                        <th class="text-center">Status</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payments as $payment)
                                        <tr>
                                            <td><code>{{ $payment->payment_code }}</code></td>
                                            <td>{{ $payment->booking_code }}</td>
                                            <td>{{ $payment->customer_name }}</td>
                                            <td>{{ $payment->payment_method_name ?? '-' }}</td>
                                            <td class="text-right">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $payment->status === 'paid' ? 'success' : ($payment->status === 'refunded' ? 'danger' : 'secondary') }}">
                                                    {{ str($payment->status)->headline() }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($payment->paid_at ?? $payment->created_at)->format('d M Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Belum ada transaksi untuk filter yang dipilih.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($payments->hasPages())
                        <div class="card-footer">{{ $payments->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
