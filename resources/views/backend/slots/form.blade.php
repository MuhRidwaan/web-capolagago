@extends('backend.main_backend')

@section('title', $slot ? 'Edit Slot' : 'Tambah Slot')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $slot ? 'Edit Slot' : 'Tambah Slot' }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.slots.index') }}">Slot Ketersediaan</a></li>
                    <li class="breadcrumb-item active">{{ $slot ? 'Edit' : 'Tambah' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    <div class="row">
        <div class="col-lg-7">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        {{ $slot ? 'Edit Slot — ' . \Carbon\Carbon::parse($slot->slot_date)->translatedFormat('d M Y') : 'Slot Baru' }}
                    </h3>
                </div>

                @if($slot)
                    {{-- EDIT MODE --}}
                    <form action="{{ route('admin.slots.update', $slot->id) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="card-body">

                            {{-- Info readonly --}}
                            <div class="form-group">
                                <label>Produk</label>
                                <input type="text" class="form-control" readonly
                                    value="{{ collect($products)->firstWhere('id', $slot->product_id)?->name ?? '—' }}">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Tanggal</label>
                                        <input type="text" class="form-control" readonly
                                            value="{{ \Carbon\Carbon::parse($slot->slot_date)->translatedFormat('D, d M Y') }}">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Jam Mulai</label>
                                        <input type="time" name="start_time" class="form-control"
                                            value="{{ old('start_time', $slot->start_time ? \Carbon\Carbon::parse($slot->start_time)->format('H:i') : '') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Total Slot <span class="text-danger">*</span></label>
                                        <input type="number" name="total_slots"
                                            class="form-control @error('total_slots') is-invalid @enderror"
                                            value="{{ old('total_slots', $slot->total_slots) }}"
                                            min="{{ $slot->booked_slots }}" required>
                                        <small class="text-muted">
                                            Minimum: {{ $slot->booked_slots }} (sudah dipesan)
                                        </small>
                                        @error('total_slots')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Sudah Dipesan</label>
                                        <input type="text" class="form-control" readonly
                                            value="{{ $slot->booked_slots }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="icheck-danger">
                                    <input type="checkbox" id="is_blocked" name="is_blocked"
                                        value="1" {{ old('is_blocked', $slot->is_blocked) ? 'checked' : '' }}>
                                    <label for="is_blocked">
                                        Blokir slot ini
                                        <small class="text-muted">(tidak bisa dipesan meski masih ada sisa)</small>
                                    </label>
                                </div>
                            </div>

                            {{-- Progress bar --}}
                            @php
                                $pct = $slot->total_slots > 0
                                    ? round(($slot->booked_slots / $slot->total_slots) * 100) : 0;
                                $barClass = $pct >= 100 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
                            @endphp
                            <div class="form-group mb-0">
                                <label>Kapasitas Terpakai</label>
                                <div class="progress" style="height:20px">
                                    <div class="progress-bar bg-{{ $barClass }}" style="width:{{ $pct }}%">
                                        {{ $pct }}%
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('admin.slots.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>

                @else
                    {{-- CREATE MODE --}}
                    <form action="{{ route('admin.slots.store') }}" method="POST">
                        @csrf
                        <div class="card-body">

                            <div class="form-group">
                                <label>Produk <span class="text-danger">*</span></label>
                                <select name="product_id" id="product_id"
                                    class="form-control @error('product_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}"
                                            data-capacity="{{ $p->max_capacity }}"
                                            {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" name="slot_date"
                                            class="form-control @error('slot_date') is-invalid @enderror"
                                            value="{{ old('slot_date') }}"
                                            min="{{ today()->toDateString() }}" required>
                                        @error('slot_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Jam Mulai <small class="text-muted">(opsional)</small></label>
                                        <input type="time" name="start_time"
                                            class="form-control @error('start_time') is-invalid @enderror"
                                            value="{{ old('start_time') }}">
                                        <small class="text-muted">Kosongkan jika tidak ada jam spesifik.</small>
                                        @error('start_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Total Slot <span class="text-danger">*</span></label>
                                <input type="number" name="total_slots" id="total_slots"
                                    class="form-control @error('total_slots') is-invalid @enderror"
                                    value="{{ old('total_slots', 1) }}" min="1" required>
                                <small class="text-muted" id="capacity-hint"></small>
                                @error('total_slots')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-0">
                                <div class="icheck-danger">
                                    <input type="checkbox" id="is_blocked" name="is_blocked"
                                        value="1" {{ old('is_blocked') ? 'checked' : '' }}>
                                    <label for="is_blocked">Blokir slot ini sejak awal</label>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('admin.slots.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Simpan
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        {{-- Panel Info --}}
        <div class="col-lg-5">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Panduan</h3>
                </div>
                <div class="card-body" style="font-size:0.875rem; line-height:1.8">
                    <p class="font-weight-bold mb-1">Total Slot</p>
                    <p class="text-muted">Jumlah unit/kapasitas yang tersedia untuk tanggal ini. Contoh: jika produk punya 5 tenda glamping, isi 5.</p>
                    <p class="font-weight-bold mb-1">Jam Mulai</p>
                    <p class="text-muted">Isi jika produk punya jadwal spesifik (misal: rafting jam 08:00 dan 13:00). Kosongkan untuk produk penginapan/camping.</p>
                    <p class="font-weight-bold mb-1">Blokir Slot</p>
                    <p class="text-muted mb-0">Gunakan untuk menutup tanggal tertentu (libur, maintenance, dll) tanpa menghapus slot.</p>
                </div>
            </div>

            @if($slot)
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Perhatian</h3>
                </div>
                <div class="card-body" style="font-size:0.875rem">
                    <p class="mb-1">Slot ini sudah memiliki <strong>{{ $slot->booked_slots }} booking</strong>.</p>
                    <p class="mb-0 text-muted">Total slot tidak bisa dikurangi di bawah jumlah booking yang sudah ada.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('backend/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('backend/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(function () {
    // Select2 untuk dropdown produk (hanya di mode create)
    $('select[name=product_id]').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: '-- Pilih Produk --',
    });

    // Auto-fill total_slots dari max_capacity produk
    $('select[name=product_id]').on('change', function () {
        const cap = $(this).find(':selected').data('capacity');
        if (cap) {
            $('#total_slots').val(cap);
            $('#capacity-hint').text('Max kapasitas produk: ' + cap + ' unit.');
        }
    });

    // Flash SweetAlert2
    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 3000, showConfirmButton: false });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
    @endif
});
</script>
@endpush
