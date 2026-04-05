@extends('backend.main_backend')

@section('title', 'Performa Produk')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Performa Produk</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Performa Produk</li>
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
                <form method="GET" action="{{ route('admin.reports.products') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Dari</label>
                                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Sampai</label>
                                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Kategori Produk</label>
                                <select name="category_id" class="form-control">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap" style="gap:.5rem;">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.reports.products') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format((float) ($summary->active_products ?? 0), 0, ',', '.') }}</h3>
                        <p>Produk Aktif Terjual</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ number_format((float) ($summary->total_bookings ?? 0), 0, ',', '.') }}</h3>
                        <p>Total Booking</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format((float) ($summary->total_qty ?? 0), 0, ',', '.') }}</h3>
                        <p>Total Qty Terjual</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>Rp {{ number_format((float) ($summary->total_revenue ?? 0), 0, ',', '.') }}</h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title mb-0">Rekap per Kategori</h3></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Kategori</th>
                                        <th class="text-right">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($topCategories as $category)
                                        <tr>
                                            <td>{{ $category->category_name }}</td>
                                            <td class="text-right">Rp {{ number_format((float) $category->total_revenue, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">Belum ada data performa pada periode ini.</td>
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
                    <div class="card-header"><h3 class="card-title mb-0">Ranking Produk</h3></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Kategori</th>
                                        <th>Mitra</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Booking</th>
                                        <th class="text-right">Revenue</th>
                                        <th class="text-right">Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                        <tr>
                                            <td>{{ $product->product_name }}</td>
                                            <td>{{ $product->category_name }}</td>
                                            <td>{{ $product->mitra_name }}</td>
                                            <td class="text-right">{{ number_format((float) $product->total_qty, 0, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format((float) $product->booking_count, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format((float) $product->total_revenue, 0, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format((float) $product->rating_avg, 2, ',', '.') }} <span class="text-muted">({{ number_format((float) $product->review_count, 0, ',', '.') }})</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Belum ada data produk untuk filter yang dipilih.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($products->hasPages())
                        <div class="card-footer">{{ $products->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
