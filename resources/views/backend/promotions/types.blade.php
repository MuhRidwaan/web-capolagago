@extends('backend.main_backend')
@section('title', 'Tipe Promo')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Tipe Promo</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.promotions.index') }}">Promo</a></li>
                    <li class="breadcrumb-item active">Tipe Promo</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title">Daftar Tipe Promo</h3></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Kode</th>
                        <th>Tipe Diskon</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($types as $t)
                    <tr>
                        <td>{{ $t->id }}</td>
                        <td><strong>{{ $t->name }}</strong></td>
                        <td><code>{{ $t->code }}</code></td>
                        <td>
                            @if($t->discount_type === 'percent')
                                <span class="badge badge-info">Persentase (%)</span>
                            @else
                                <span class="badge badge-warning">Nominal (Rp)</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $t->description ?? '-' }}</td>
                        <td>
                            @if($t->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted" style="font-size:0.85rem">
            Tipe promo bersifat sistem dan tidak dapat diubah melalui UI. Hubungi developer untuk menambah tipe baru.
        </div>
    </div>
</div>
</section>
@endsection
