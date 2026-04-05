@extends('backend.main_backend')
@section('title', 'Akses Ditolak')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Akses Ditolak</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">403</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    <div class="row justify-content-center mt-4">
        <div class="col-md-6 text-center">
            <div class="card card-outline card-danger">
                <div class="card-body py-5">
                    <i class="fas fa-lock fa-4x text-danger mb-3"></i>
                    <h2 class="font-weight-bold">403 — Akses Ditolak</h2>
                    <p class="text-muted mt-2 mb-4">
                        {{ $message ?? 'Kamu tidak memiliki izin untuk mengakses halaman ini.' }}
                    </p>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-home mr-1"></i>Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection
