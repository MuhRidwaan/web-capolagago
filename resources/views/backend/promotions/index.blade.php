@extends('backend.main_backend')
@section('title', 'Voucher & Promo')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Voucher & Promo</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Promo</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    {{-- Filter --}}
    <div class="card card-outline card-secondary mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline flex-wrap gap-2">
                <input type="text" name="q" class="form-control form-control-sm mr-2 mb-1"
                    placeholder="Cari nama / kode..." value="{{ request('q') }}" style="min-width:200px">
                <select name="type" class="form-control form-control-sm mr-2 mb-1">
                    <option value="">Semua Tipe</option>
                    @foreach($promoTypes as $t)
                        <option value="{{ $t->id }}" {{ request('type') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control form-control-sm mr-2 mb-1">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary mb-1"><i class="fas fa-search mr-1"></i>Filter</button>
                <a href="{{ route('admin.promotions.index') }}" class="btn btn-sm btn-secondary mb-1">Reset</a>
                <a href="{{ route('admin.promotions.create') }}" class="btn btn-sm btn-success mb-1 ml-auto">
                    <i class="fas fa-plus mr-1"></i>Buat Promo
                </a>
            </form>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama / Kode</th>
                            <th>Tipe</th>
                            <th>Diskon</th>
                            <th>Min. Order</th>
                            <th>Kuota</th>
                            <th>Masa Berlaku</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promotions as $p)
                        @php
                            $now = now();
                            $isExpired = $now->gt($p->valid_until);
                            $isNotStarted = $now->lt($p->valid_from);
                        @endphp
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $p->name }}</div>
                                <code class="text-sm">{{ $p->code }}</code>
                            </td>
                            <td><span class="badge badge-info">{{ $p->type_name }}</span></td>
                            <td>
                                @if($p->discount_type === 'percent')
                                    {{ number_format($p->discount_value, 0) }}%
                                    @if($p->max_discount_amount)
                                        <small class="text-muted d-block">maks Rp {{ number_format($p->max_discount_amount, 0, ',', '.') }}</small>
                                    @endif
                                @else
                                    Rp {{ number_format($p->discount_value, 0, ',', '.') }}
                                @endif
                            </td>
                            <td>Rp {{ number_format($p->min_order_amount, 0, ',', '.') }}</td>
                            <td>
                                @if($p->quota)
                                    {{ $p->used_count }} / {{ $p->quota }}
                                    <div class="progress mt-1" style="height:4px">
                                        <div class="progress-bar {{ $p->used_count >= $p->quota ? 'bg-danger' : 'bg-success' }}"
                                            style="width:{{ min(100, round($p->used_count / $p->quota * 100)) }}%"></div>
                                    </div>
                                @else
                                    <span class="text-muted">∞ tak terbatas</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ \Carbon\Carbon::parse($p->valid_from)->format('d M Y') }}</small>
                                <small class="text-muted"> s/d </small>
                                <small>{{ \Carbon\Carbon::parse($p->valid_until)->format('d M Y') }}</small>
                                @if($isExpired)
                                    <span class="badge badge-danger d-block mt-1">Kedaluwarsa</span>
                                @elseif($isNotStarted)
                                    <span class="badge badge-warning d-block mt-1">Belum mulai</span>
                                @endif
                            </td>
                            <td>
                                @if($p->is_active && !$isExpired)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('admin.promotions.edit', $p->id) }}"
                                    class="btn btn-xs btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.promotions.toggle', $p->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs {{ $p->is_active ? 'btn-secondary' : 'btn-success' }}"
                                        title="{{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="fas fa-{{ $p->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.promotions.destroy', $p->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Hapus promo {{ addslashes($p->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada promo.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($promotions->hasPages())
        <div class="card-footer">
            {{ $promotions->links() }}
        </div>
        @endif
    </div>
</div>
</section>
@endsection
