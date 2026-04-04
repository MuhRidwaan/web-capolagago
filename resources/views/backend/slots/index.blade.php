@extends('backend.main_backend')

@section('title', 'Manajemen Slot Ketersediaan')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Slot Ketersediaan</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Slot Ketersediaan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    {{-- Filter --}}
    <div class="card card-outline card-secondary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.slots.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="text-sm">Produk</label>
                            <select name="product_id" id="filter-product" class="form-control form-control-sm">
                                <option value="">Semua Produk</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="text-sm">Dari Tanggal</label>
                            <input type="date" name="date_from" class="form-control form-control-sm"
                                value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="text-sm">Sampai Tanggal</label>
                            <input type="date" name="date_to" class="form-control form-control-sm"
                                value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="text-sm">Status</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia</option>
                                <option value="full"      {{ request('status') === 'full'      ? 'selected' : '' }}>Penuh</option>
                                <option value="blocked"   {{ request('status') === 'blocked'   ? 'selected' : '' }}>Diblokir</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary mr-2">
                            <i class="fas fa-search mr-1"></i>Cari
                        </button>
                        <a href="{{ route('admin.slots.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('admin.slots.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i>Tambah Slot
            </a>
            <button type="button" class="btn btn-info btn-sm ml-1" data-toggle="modal" data-target="#modalGenerate">
                <i class="fas fa-magic mr-1"></i>Generate Bulk
            </button>
        </div>
        {{-- Bulk action toolbar (muncul saat ada yang dicentang) --}}
        <div id="bulk-toolbar" class="d-none">
            <form id="form-bulk" method="POST">
                @csrf
                <div class="input-group input-group-sm">
                    <select name="bulk_action" id="bulk_action" class="form-control">
                        <option value="">-- Pilih Aksi --</option>
                        <option value="set_slots">Set Total Slot</option>
                        <option value="block">Blokir</option>
                        <option value="unblock">Buka Blokir</option>
                        <option value="delete">Hapus</option>
                    </select>
                    <input type="number" name="total_slots" id="bulk_total_slots"
                        class="form-control d-none" placeholder="Jumlah slot" min="1">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-warning" id="btn-bulk-apply">
                            Terapkan ke <span id="selected-count">0</span> slot
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card">
        <div class="card-body p-0">
            <form id="form-select-all">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="check-all">
                        </th>
                        <th>Produk</th>
                        <th>Tanggal</th>
                        <th>Jam Mulai</th>
                        <th class="text-center">Total Slot</th>
                        <th class="text-center">Dipesan</th>
                        <th class="text-center">Sisa</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slots as $slot)
                    @php
                        $sisa = $slot->total_slots - $slot->booked_slots;
                        $pct  = $slot->total_slots > 0
                            ? round(($slot->booked_slots / $slot->total_slots) * 100)
                            : 0;
                    @endphp
                    <tr class="{{ $slot->is_blocked ? 'table-secondary' : '' }}">
                        <td>
                            <input type="checkbox" class="slot-check" name="slot_ids[]"
                                value="{{ $slot->id }}" form="form-bulk">
                        </td>
                        <td>
                            <span class="font-weight-medium">{{ $slot->product_name }}</span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($slot->slot_date)->translatedFormat('D, d M Y') }}</td>
                        <td>{{ $slot->start_time ? \Carbon\Carbon::parse($slot->start_time)->format('H:i') : '<span class="text-muted">—</span>' }}</td>
                        <td class="text-center">{{ $slot->total_slots }}</td>
                        <td class="text-center">{{ $slot->booked_slots }}</td>
                        <td class="text-center">
                            @if($slot->is_blocked)
                                <span class="text-muted">—</span>
                            @elseif($sisa === 0)
                                <span class="text-danger font-weight-bold">0</span>
                            @else
                                <span class="text-success">{{ $sisa }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($slot->is_blocked)
                                <span class="badge badge-secondary">Diblokir</span>
                            @elseif($sisa <= 0)
                                <span class="badge badge-danger">Penuh</span>
                            @elseif($pct >= 70)
                                <span class="badge badge-warning">Hampir Penuh</span>
                            @else
                                <span class="badge badge-success">Tersedia</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.slots.edit', $slot->id) }}"
                                class="btn btn-xs btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($slot->booked_slots == 0)
                            <form action="{{ route('admin.slots.destroy', $slot->id) }}"
                                method="POST" class="d-inline form-delete-slot">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                            Belum ada slot. Tambah manual atau gunakan Generate Bulk.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </form>
        </div>
        @if($slots->hasPages())
        <div class="card-footer">
            {{ $slots->links() }}
        </div>
        @endif
    </div>

</div>
</section>

{{-- Modal Generate Bulk --}}
<div class="modal fade" id="modalGenerate" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.slots.generate') }}" method="POST">
                @csrf
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-magic mr-2"></i>Generate Slot Bulk
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Produk <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-control" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}"
                                    data-capacity="{{ $p->max_capacity }}">
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Dari Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="date_from" class="form-control"
                                    min="{{ today()->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Sampai Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="date_to" class="form-control"
                                    min="{{ today()->toDateString() }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Jam Mulai <small class="text-muted">(opsional)</small></label>
                                <input type="time" name="start_time" class="form-control">
                                <small class="text-muted">Kosongkan jika tidak ada jam spesifik.</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Total Slot per Hari <span class="text-danger">*</span></label>
                                <input type="number" name="total_slots" id="gen_total_slots"
                                    class="form-control" min="1" value="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Lewati Hari</label>
                        <div class="row">
                            @foreach(['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $i => $day)
                            <div class="col-6">
                                <div class="icheck-primary">
                                    <input type="checkbox" id="skip_{{ $i }}"
                                        name="skip_days[]" value="{{ $i }}">
                                    <label for="skip_{{ $i }}">{{ $day }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-magic mr-1"></i>Generate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('backend/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
(function () {
    // Select2 untuk filter produk
    $('#filter-product').select2({ theme: 'bootstrap4', width: '100%' });

    // Select2 untuk dropdown produk di modal generate
    $('#modalGenerate select[name=product_id]').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: '-- Pilih Produk --',
    });

    // Select2 untuk dropdown status inline bulk action
    $('#bulk_action').select2({
        theme: 'bootstrap4',
        minimumResultsForSearch: Infinity,
        width: 'auto',
    });

    // Check all
    const checkAll   = document.getElementById('check-all');
    const checkboxes = document.querySelectorAll('.slot-check');
    const bulkBar    = document.getElementById('bulk-toolbar');
    const countEl    = document.getElementById('selected-count');
    const formBulk   = document.getElementById('form-bulk');
    const bulkAction = document.getElementById('bulk_action');
    const bulkSlots  = document.getElementById('bulk_total_slots');

    function updateBulkBar() {
        const checked = document.querySelectorAll('.slot-check:checked').length;
        bulkBar.classList.toggle('d-none', checked === 0);
        countEl.textContent = checked;
    }

    checkAll.addEventListener('change', function () {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateBulkBar));

    // Tampilkan input total_slots hanya saat aksi set_slots
    $(bulkAction).on('change', function () {
        bulkSlots.classList.toggle('d-none', this.value !== 'set_slots');
    });

    // Arahkan form ke endpoint yang benar + konfirmasi SweetAlert2
    document.getElementById('btn-bulk-apply')?.closest('form')
        .addEventListener('submit', function (e) {
            e.preventDefault();
            const action = bulkAction.value;
            if (! action) {
                Swal.fire({ icon: 'warning', title: 'Pilih aksi terlebih dahulu.', timer: 2000, showConfirmButton: false });
                return;
            }

            const count = document.querySelectorAll('.slot-check:checked').length;
            const labels = { set_slots: 'Set Total Slot', block: 'Blokir', unblock: 'Buka Blokir', delete: 'Hapus' };

            Swal.fire({
                title: `${labels[action]} ${count} slot?`,
                text: action === 'delete' ? 'Slot dengan booking tidak akan dihapus.' : '',
                icon: action === 'delete' ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: action === 'delete' ? '#dc3545' : '#007bff',
            }).then(result => {
                if (! result.isConfirmed) return;
                if (action === 'delete') {
                    this.action = '{{ route('admin.slots.bulk-destroy') }}';
                } else {
                    this.action = '{{ route('admin.slots.bulk-update') }}';
                }
                this.submit();
            });
        });

    // Konfirmasi hapus single slot
    document.querySelectorAll('.form-delete-slot').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const f = this;
            Swal.fire({
                title: 'Hapus slot ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
            }).then(r => { if (r.isConfirmed) f.submit(); });
        });
    });

    // Auto-fill total_slots dari max_capacity produk di modal generate
    $('#modalGenerate select[name=product_id]').on('change', function () {
        const cap = $(this).find(':selected').data('capacity');
        if (cap) $('#gen_total_slots').val(cap);
    });

    // Flash SweetAlert2
    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 3000, showConfirmButton: false });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
    @endif
})();
</script>
@endpush
