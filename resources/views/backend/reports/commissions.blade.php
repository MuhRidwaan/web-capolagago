@extends('backend.main_backend')

@section('title', 'Laporan Komisi')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Laporan Komisi</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Laporan Komisi</li>
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
                <form method="GET" action="{{ route('admin.reports.commissions') }}">
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
                                <label>Status Komisi</label>
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
                                <label>Mitra</label>
                                <select name="mitra_id" class="form-control">
                                    <option value="">Semua Mitra</option>
                                    @foreach ($mitras as $mitra)
                                        <option value="{{ $mitra->id }}" @selected((string) request('mitra_id') === (string) $mitra->id)>{{ $mitra->business_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap" style="gap:.5rem;">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.reports.commissions') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format((float) ($summary->total_rows ?? 0), 0, ',', '.') }}</h3>
                        <p>Total Item Komisi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>Rp {{ number_format((float) ($summary->total_gross ?? 0), 0, ',', '.') }}</h3>
                        <p>Gross Amount</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>Rp {{ number_format((float) ($summary->total_commission ?? 0), 0, ',', '.') }}</h3>
                        <p>Total Komisi Platform</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>Rp {{ number_format((float) ($summary->total_net ?? 0), 0, ',', '.') }}</h3>
                        <p>Hak Mitra</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title mb-0">Rekap per Mitra</h3></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Mitra</th>
                                        <th class="text-right">Komisi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($byMitra as $row)
                                        <tr>
                                            <td>{{ $row->business_name }}</td>
                                            <td class="text-right">Rp {{ number_format((float) $row->commission_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">Belum ada data komisi pada periode ini.</td>
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
                    <div class="card-header"><h3 class="card-title mb-0">Detail Komisi</h3></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Booking</th>
                                        <th>Mitra</th>
                                        <th>Produk</th>
                                        <th class="text-right">Gross</th>
                                        <th class="text-right">Komisi</th>
                                        <th class="text-right">Net</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($commissions as $commission)
                                        <tr>
                                            <td>{{ $commission->booking_code }}</td>
                                            <td>{{ $commission->business_name }}</td>
                                            <td>
                                                {{ $commission->product_name }}
                                                <div class="text-muted small">{{ $commission->quantity }} qty • {{ number_format((float) $commission->commission_rate, 2, ',', '.') }}%</div>
                                            </td>
                                            <td class="text-right">Rp {{ number_format((float) $commission->gross_amount, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format((float) $commission->commission_amount, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format((float) $commission->net_amount, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $commission->status === 'settled' ? 'success' : ($commission->status === 'processed' ? 'info' : ($commission->status === 'cancelled' ? 'danger' : 'secondary')) }}">
                                                    {{ str($commission->status)->headline() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Belum ada detail komisi untuk filter yang dipilih.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($commissions->hasPages())
                        <div class="card-footer">{{ $commissions->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
