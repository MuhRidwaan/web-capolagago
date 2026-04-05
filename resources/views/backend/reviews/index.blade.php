@extends('backend.main_backend')
@section('title', 'Ulasan & Rating')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Ulasan & Rating</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Ulasan & Rating</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    {{-- Summary --}}
    <div class="row mb-3">
        {{-- Rata-rata rating --}}
        <div class="col-md-3">
            <div class="card card-outline card-warning text-center">
                <div class="card-body py-3">
                    <div style="font-size:2.5rem; font-weight:700; color:#f39c12; line-height:1">
                        {{ number_format($avgRating ?? 0, 1) }}
                    </div>
                    <div class="text-warning mt-1">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star{{ $i <= round($avgRating ?? 0) ? '' : '-o' }}" style="font-size:0.9rem"></i>
                        @endfor
                    </div>
                    <small class="text-muted">Rata-rata Rating</small>
                </div>
            </div>
        </div>
        {{-- Per bintang --}}
        @for($star = 5; $star >= 1; $star--)
        <div class="col-md-1 col-4">
            <a href="{{ route('admin.reviews.index', ['rating' => $star]) }}" class="text-decoration-none">
                <div class="small-box {{ request('rating') == $star ? 'bg-warning' : 'bg-light border' }}" style="min-height:70px">
                    <div class="inner py-2 px-2 text-center">
                        <h4 class="mb-0 {{ request('rating') == $star ? 'text-white' : 'text-dark' }}">
                            {{ $ratingCounts[$star] ?? 0 }}
                        </h4>
                        <p class="mb-0 {{ request('rating') == $star ? 'text-white' : 'text-muted' }}" style="font-size:0.7rem">
                            @for($i = 0; $i < $star; $i++)<i class="fas fa-star" style="font-size:0.6rem"></i>@endfor
                        </p>
                    </div>
                </div>
            </a>
        </div>
        @endfor
    </div>

    {{-- Filter & Toolbar --}}
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
        <form class="form-inline" method="GET">
            <div class="input-group input-group-sm mr-2 mb-1">
                <input type="text" name="q" class="form-control" placeholder="Nama / produk / komentar"
                    value="{{ request('q') }}" style="width:200px">
                <div class="input-group-append">
                    <button class="btn btn-default"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <select name="product_id" id="filter-product" class="form-control form-control-sm mr-2 mb-1" style="width:180px">
                <option value="">Semua Produk</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
            <select name="rating" class="form-control form-control-sm mr-2 mb-1" style="width:110px">
                <option value="">Semua Rating</option>
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                        {{ $i }} Bintang
                    </option>
                @endfor
            </select>
            <select name="status" class="form-control form-control-sm mr-2 mb-1" style="width:130px">
                <option value="">Semua Status</option>
                <option value="published"   {{ request('status') === 'published'   ? 'selected' : '' }}>Dipublikasi</option>
                <option value="unpublished" {{ request('status') === 'unpublished' ? 'selected' : '' }}>Disembunyikan</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary mr-1 mb-1">Filter</button>
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-secondary mb-1">Reset</a>
        </form>

        {{-- Bulk toolbar --}}
        <div id="bulk-toolbar" class="d-none">
            <form id="form-bulk" action="{{ route('admin.reviews.bulk-toggle') }}" method="POST">
                @csrf
                <div class="input-group input-group-sm">
                    <select name="action" id="bulk-action" class="form-control">
                        <option value="">-- Aksi --</option>
                        <option value="publish">Publikasikan</option>
                        <option value="unpublish">Sembunyikan</option>
                    </select>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-warning" id="btn-bulk">
                            Terapkan ke <span id="selected-count">0</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="40"><input type="checkbox" id="check-all"></th>
                        <th>Tamu</th>
                        <th>Produk</th>
                        <th class="text-center">Rating</th>
                        <th>Komentar</th>
                        <th>Booking</th>
                        <th>Tanggal</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $r)
                    <tr class="{{ ! $r->is_published ? 'table-secondary' : '' }}">
                        <td>
                            <input type="checkbox" class="review-check" name="review_ids[]"
                                value="{{ $r->id }}" form="form-bulk">
                        </td>
                        <td>
                            <div class="font-weight-medium">{{ $r->user_name }}</div>
                            <small class="text-muted">{{ $r->user_email }}</small>
                        </td>
                        <td>
                            <a href="{{ route('admin.products.edit', $r->product_id) }}" class="text-sm">
                                {{ $r->product_name }}
                            </a>
                        </td>
                        <td class="text-center">
                            <div class="text-warning" style="font-size:0.8rem; white-space:nowrap">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star{{ $i <= $r->rating ? '' : ' text-muted' }}"></i>
                                @endfor
                            </div>
                            <small class="text-muted">{{ $r->rating }}/5</small>
                        </td>
                        <td style="max-width:250px">
                            @if($r->comment)
                                <span class="d-inline-block text-truncate" style="max-width:230px"
                                    title="{{ $r->comment }}">
                                    {{ $r->comment }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.bookings.show', $r->booking_code) }}" class="text-xs">
                                {{ $r->booking_code }}
                            </a>
                        </td>
                        <td class="text-xs text-muted">
                            {{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}
                        </td>
                        <td class="text-center">
                            <form action="{{ route('admin.reviews.toggle', $r->id) }}"
                                method="POST" class="d-inline form-toggle">
                                @csrf
                                <button type="submit"
                                    class="btn btn-xs {{ $r->is_published ? 'btn-success' : 'btn-outline-secondary' }}">
                                    {{ $r->is_published ? 'Publik' : 'Hidden' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.reviews.show', $r->id) }}"
                                class="btn btn-xs btn-outline-info" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('admin.reviews.destroy', $r->id) }}"
                                method="POST" class="d-inline form-delete">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-star fa-2x mb-2 d-block"></i>
                            Belum ada ulasan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
        <div class="card-footer">{{ $reviews->links() }}</div>
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
    $('#filter-product').select2({ theme: 'bootstrap4', width: '180px' });

    // Check all
    const checkAll  = document.getElementById('check-all');
    const checks    = document.querySelectorAll('.review-check');
    const bulkBar   = document.getElementById('bulk-toolbar');
    const countEl   = document.getElementById('selected-count');

    function updateBulk() {
        const n = document.querySelectorAll('.review-check:checked').length;
        bulkBar.classList.toggle('d-none', n === 0);
        countEl.textContent = n;
    }

    checkAll.addEventListener('change', function () {
        checks.forEach(c => c.checked = this.checked);
        updateBulk();
    });
    checks.forEach(c => c.addEventListener('change', updateBulk));

    // Bulk submit
    $('#form-bulk').on('submit', function (e) {
        e.preventDefault();
        const action = $('#bulk-action').val();
        if (! action) { Swal.fire({ icon: 'warning', title: 'Pilih aksi dulu.', timer: 2000, showConfirmButton: false }); return; }
        const label = action === 'publish' ? 'publikasikan' : 'sembunyikan';
        const count = document.querySelectorAll('.review-check:checked').length;
        Swal.fire({
            title: `${label.charAt(0).toUpperCase() + label.slice(1)} ${count} ulasan?`,
            icon: 'question', showCancelButton: true,
            confirmButtonText: 'Ya', cancelButtonText: 'Batal',
        }).then(r => { if (r.isConfirmed) this.submit(); });
    });

    // Toggle publish
    $('.form-toggle').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const isPublic = $(form).find('button').hasClass('btn-success');
        Swal.fire({
            title: isPublic ? 'Sembunyikan ulasan ini?' : 'Publikasikan ulasan ini?',
            icon: 'question', showCancelButton: true,
            confirmButtonText: 'Ya', cancelButtonText: 'Batal',
            confirmButtonColor: isPublic ? '#6c757d' : '#28a745',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });

    // Hapus
    $('.form-delete').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Hapus ulasan ini?', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal', confirmButtonColor: '#dc3545',
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
