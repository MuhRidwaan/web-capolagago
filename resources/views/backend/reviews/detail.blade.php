@extends('backend.main_backend')
@section('title', 'Detail Ulasan')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Detail Ulasan</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">Ulasan</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-{{ $review->is_published ? 'success' : 'secondary' }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-star mr-2 text-warning"></i>Ulasan Produk
                    </h3>
                    <span class="badge badge-{{ $review->is_published ? 'success' : 'secondary' }} px-3 py-2">
                        {{ $review->is_published ? 'Dipublikasikan' : 'Disembunyikan' }}
                    </span>
                </div>
                <div class="card-body">
                    {{-- Rating bintang --}}
                    <div class="text-center mb-4">
                        <div class="text-warning" style="font-size:2rem">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star{{ $i <= $review->rating ? '' : ' text-muted' }}"></i>
                            @endfor
                        </div>
                        <div class="font-weight-bold mt-1" style="font-size:1.2rem">
                            {{ $review->rating }} / 5
                        </div>
                    </div>

                    {{-- Komentar --}}
                    <div class="bg-light rounded p-3 mb-4">
                        @if($review->comment)
                            <p class="mb-0" style="font-size:1rem; line-height:1.7">
                                "{{ $review->comment }}"
                            </p>
                        @else
                            <p class="text-muted mb-0 text-center">
                                <i class="fas fa-comment-slash mr-1"></i>Tidak ada komentar.
                            </p>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="120">Tamu</td>
                                    <td><strong>{{ $review->user_name }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email</td>
                                    <td>{{ $review->user_email }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tanggal</td>
                                    <td>{{ \Carbon\Carbon::parse($review->created_at)->translatedFormat('d F Y, H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="120">Produk</td>
                                    <td>
                                        <a href="{{ route('admin.products.edit', $review->product_id) }}">
                                            {{ $review->product_name }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Booking</td>
                                    <td>
                                        <a href="{{ route('admin.bookings.show', $review->booking_id) }}">
                                            {{ $review->booking_code }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tgl Kunjungan</td>
                                    <td>{{ \Carbon\Carbon::parse($review->visit_date)->translatedFormat('d F Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aksi --}}
        <div class="col-lg-4">
            <div class="card card-outline card-{{ $review->is_published ? 'warning' : 'success' }}">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-toggle-on mr-2"></i>Moderasi
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted text-sm mb-3">
                        @if($review->is_published)
                            Ulasan ini sedang ditampilkan ke publik. Sembunyikan jika mengandung konten tidak pantas.
                        @else
                            Ulasan ini sedang disembunyikan. Publikasikan jika sudah sesuai.
                        @endif
                    </p>
                    <form action="{{ route('admin.reviews.toggle', $review->id) }}"
                        method="POST" id="form-toggle">
                        @csrf
                        <button type="submit"
                            class="btn btn-{{ $review->is_published ? 'warning' : 'success' }} btn-block">
                            <i class="fas fa-{{ $review->is_published ? 'eye-slash' : 'eye' }} mr-1"></i>
                            {{ $review->is_published ? 'Sembunyikan' : 'Publikasikan' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-trash mr-2"></i>Hapus Ulasan</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted text-sm mb-3">Hapus permanen ulasan ini. Tindakan tidak bisa dibatalkan.</p>
                    <form action="{{ route('admin.reviews.destroy', $review->id) }}"
                        method="POST" id="form-delete">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash mr-1"></i>Hapus Ulasan
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
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
    $('#form-toggle').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const isPublic = {{ $review->is_published ? 'true' : 'false' }};
        Swal.fire({
            title: isPublic ? 'Sembunyikan ulasan ini?' : 'Publikasikan ulasan ini?',
            icon: 'question', showCancelButton: true,
            confirmButtonText: 'Ya', cancelButtonText: 'Batal',
            confirmButtonColor: isPublic ? '#ffc107' : '#28a745',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });

    $('#form-delete').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Hapus ulasan ini?',
            text: 'Tindakan ini tidak bisa dibatalkan.',
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
        }).then(r => {
            if (r.isConfirmed) form.submit();
        });
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
