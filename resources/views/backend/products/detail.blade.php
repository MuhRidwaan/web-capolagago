@extends('backend.main_backend')
@section('title', 'Detail Produk — ' . $product->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/ekko-lightbox/ekko-lightbox.css') }}">
<style>
    .img-thumb {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
        transition: opacity .2s;
    }
    .img-thumb:hover { opacity: .8; }
    .img-primary-badge {
        position: absolute;
        top: 6px; left: 6px;
        font-size: .65rem;
    }
    .tag-badge {
        font-size: .75rem;
        margin: 2px;
    }
    .star-filled { color: #f39c12; }
    .star-empty  { color: #dee2e6; }
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Detail Produk</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Data Produk</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($product->name, 30) }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    <div class="row">

        {{-- ── Kolom Kiri ── --}}
        <div class="col-lg-8">

            {{-- Galeri Gambar --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-images mr-2"></i>Galeri Foto</h3>
                    <span class="badge badge-secondary">{{ $product->images->count() }} foto</span>
                </div>
                <div class="card-body">
                    @if($product->images->count())
                        {{-- Foto utama --}}
                        @php $primary = $product->images->firstWhere('is_primary', true) ?? $product->images->first(); @endphp
                        <a href="{{ Storage::url($primary->image_path) }}"
                            data-toggle="lightbox" data-gallery="product-gallery"
                            data-title="{{ $primary->alt_text ?? $product->name }}">
                            <img src="{{ Storage::url($primary->image_path) }}"
                                alt="{{ $primary->alt_text ?? $product->name }}"
                                class="img-fluid rounded mb-3 w-100"
                                style="max-height:380px; object-fit:cover;">
                        </a>

                        {{-- Thumbnail grid --}}
                        @if($product->images->count() > 1)
                        <div class="row">
                            @foreach($product->images as $img)
                            <div class="col-4 col-md-3 mb-2 position-relative">
                                <a href="{{ Storage::url($img->image_path) }}"
                                    data-toggle="lightbox" data-gallery="product-gallery"
                                    data-title="{{ $img->alt_text ?? $product->name }}">
                                    <img src="{{ Storage::url($img->image_path) }}"
                                        alt="{{ $img->alt_text }}"
                                        class="img-thumb {{ $img->is_primary ? 'border border-warning' : '' }}">
                                </a>
                                @if($img->is_primary)
                                    <span class="badge badge-warning img-primary-badge">Utama</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-image fa-3x mb-3 d-block"></i>
                            Belum ada foto produk.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info Produk --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informasi Produk</h3>
                </div>
                <div class="card-body">
                    <h4 class="font-weight-bold mb-1">{{ $product->name }}</h4>
                    <p class="text-muted mb-3">{{ $product->short_desc }}</p>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="130">Kategori</td>
                                    <td>
                                        <span class="badge badge-info">{{ $product->category?->label ?? '—' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Mitra</td>
                                    <td>{{ $product->mitra?->business_name ?? 'Capolaga Internal' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Harga</td>
                                    <td>
                                        <strong class="text-primary">
                                            Rp {{ number_format($product->price, 0, ',', '.') }}
                                        </strong>
                                        <span class="text-muted">{{ $product->price_label }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tamu</td>
                                    <td>{{ $product->min_pax }} – {{ $product->max_pax }} orang</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="130">Kapasitas/Hari</td>
                                    <td>{{ $product->max_capacity }} unit</td>
                                </tr>
                                @if($product->duration_hours)
                                <tr>
                                    <td class="text-muted">Durasi</td>
                                    <td>{{ $product->duration_hours }} jam</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-muted">Rating</td>
                                    <td>
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= round($product->rating_avg) ? 'star-filled' : 'star-empty' }}" style="font-size:.8rem"></i>
                                        @endfor
                                        <span class="ml-1">{{ number_format($product->rating_avg, 1) }}</span>
                                        <span class="text-muted">({{ $product->review_count }} ulasan)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Urutan</td>
                                    <td>{{ $product->sort_order }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="bg-light rounded p-3 mb-3">
                        <p class="mb-0" style="line-height:1.8; white-space:pre-line">{{ $product->description }}</p>
                    </div>

                    {{-- Tag Aktivitas --}}
                    @if($product->activityTags->count())
                    <div>
                        <small class="text-muted d-block mb-2">Tag Aktivitas:</small>
                        @foreach($product->activityTags->groupBy('group_name') as $group => $tags)
                            <span class="text-muted text-xs mr-1">{{ ucfirst($group) }}:</span>
                            @foreach($tags as $tag)
                                <span class="badge badge-light border tag-badge">{{ $tag->name }}</span>
                            @endforeach
                            <br>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Ulasan Terbaru --}}
            @if($recentReviews->count())
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-star mr-2"></i>Ulasan Terbaru</h3>
                    <a href="{{ route('admin.reviews.index', ['product_id' => $product->id]) }}"
                        class="btn btn-xs btn-outline-info">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    @foreach($recentReviews as $review)
                    <div class="px-3 py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong class="text-sm">{{ $review->user_name }}</strong>
                                <div class="text-warning" style="font-size:.75rem">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star{{ $i <= $review->rating ? '' : ' text-muted' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($review->created_at)->format('d M Y') }}
                            </small>
                        </div>
                        @if($review->comment)
                            <p class="text-sm text-muted mb-0 mt-1">{{ $review->comment }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- ── Kolom Kanan ── --}}
        <div class="col-lg-4">

            {{-- Status & Aksi --}}
            <div class="card card-outline card-{{ $product->is_active ? 'success' : 'secondary' }}">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-toggle-on mr-2"></i>Status</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status Produk</span>
                        <span class="badge badge-{{ $product->is_active ? 'success' : 'secondary' }}">
                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Unggulan</span>
                        <span class="badge badge-{{ $product->is_featured ? 'warning' : 'light border' }}">
                            {{ $product->is_featured ? 'Unggulan' : 'Biasa' }}
                        </span>
                    </div>
                    <a href="{{ route('admin.products.edit', $product) }}"
                        class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-edit mr-1"></i>Edit Produk
                    </a>
                    <a href="{{ route('admin.slots.index', ['product_id' => $product->id]) }}"
                        class="btn btn-outline-info btn-block mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Lihat Slot
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>
            </div>

            {{-- Statistik --}}
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Statistik</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Total Booking</td>
                            <td class="text-right font-weight-bold">
                                {{ number_format($stats->total_bookings ?? 0) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Terjual</td>
                            <td class="text-right font-weight-bold">
                                {{ number_format($stats->total_qty ?? 0) }} unit
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Pendapatan</td>
                            <td class="text-right font-weight-bold text-success">
                                Rp {{ number_format($stats->total_revenue ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Rating Rata-rata</td>
                            <td class="text-right">
                                <span class="text-warning">★</span>
                                {{ number_format($product->rating_avg, 1) }}
                                <span class="text-muted">/5</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jumlah Ulasan</td>
                            <td class="text-right">{{ $product->review_count }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- SEO --}}
            @if($product->meta_title || $product->meta_desc)
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-search mr-2"></i>SEO</h3>
                </div>
                <div class="card-body" style="font-size:.875rem">
                    @if($product->meta_title)
                    <div class="mb-2">
                        <small class="text-muted d-block">Meta Judul</small>
                        {{ $product->meta_title }}
                    </div>
                    @endif
                    @if($product->meta_desc)
                    <div>
                        <small class="text-muted d-block">Meta Deskripsi</small>
                        {{ $product->meta_desc }}
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Info Teknis --}}
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-code mr-2"></i>Info Teknis</h3>
                </div>
                <div class="card-body" style="font-size:.875rem">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Slug</td>
                            <td><code>{{ $product->slug }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dibuat</td>
                            <td>{{ $product->created_at->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Diperbarui</td>
                            <td>{{ $product->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('backend/plugins/ekko-lightbox/ekko-lightbox.min.js') }}"></script>
<script>
$(document).on('click', '[data-toggle="lightbox"]', function (e) {
    e.preventDefault();
    $(this).ekkoLightbox({ alwaysShowClose: true });
});
</script>
@endpush
