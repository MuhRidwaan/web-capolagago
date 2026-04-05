@extends('backend.main_backend')
@section('title', 'Komisi Mitra')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Komisi Mitra</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Komisi Mitra</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    {{-- Summary Cards --}}
    <div class="row mb-3">
        @php
        $cards = [
            'pending'   => ['label' => 'Pending',    'color' => 'warning'],
            'processed' => ['label' => 'Diproses',   'color' => 'info'],
            'settled'   => ['label' => 'Settled',    'color' => 'success'],
            'cancelled' => ['label' => 'Dibatalkan', 'color' => 'danger'],
        ];
        @endphp
        @foreach($cards as $key => $card)
        <div class="col-6 col-md-2">
            <a href="{{ route('admin.commissions.index', ['status' => $key]) }}" class="text-decoration-none">
                <div class="small-box bg-{{ $card['color'] }}" style="min-height:80px">
                    <div class="inner py-2 px-3">
                        <h4 class="mb-0">{{ $statusCounts[$key] ?? 0 }}</h4>
                        <p class="mb-0" style="font-size:0.75rem">{{ $card['label'] }}</p>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
        <div class="col-6 col-md-2">
            <div class="small-box bg-primary" style="min-height:80px">
                <div class="inner py-2 px-3">
                    <h5 class="mb-0" style="font-size:0.85rem">
                        Rp {{ number_format($summary->total_commission ?? 0, 0, ',', '.') }}
                    </h5>
                    <p class="mb-0" style="font-size:0.75rem">Total Komisi</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="small-box bg-teal" style="min-height:80px">
                <div class="inner py-2 px-3">
                    <h5 class="mb-0" style="font-size:0.85rem">
                        Rp {{ number_format($summary->total_net ?? 0, 0, ',', '.') }}
                    </h5>
                    <p class="mb-0" style="font-size:0.75rem">Total ke Mitra</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Toolbar --}}
    <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap">
        <form class="form-inline flex-wrap" method="GET">
            <select name="mitra_id" id="filter-mitra" class="form-control form-control-sm mr-2 mb-1" style="width:200px">
                <option value="">Semua Mitra</option>
                @foreach($mitras as $m)
                    <option value="{{ $m->id }}" {{ request('mitra_id') == $m->id ? 'selected' : '' }}>
                        {{ $m->business_name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-control form-control-sm mr-2 mb-1" style="width:130px">
                <option value="">Semua Status</option>
                @foreach(['pending','processed','settled','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="date_from" class="form-control form-control-sm mr-1 mb-1"
                value="{{ request('date_from') }}" title="Dari tanggal">
            <input type="date" name="date_to" class="form-control form-control-sm mr-2 mb-1"
                value="{{ request('date_to') }}" title="Sampai tanggal">
            <button type="submit" class="btn btn-sm btn-primary mr-1 mb-1">Filter</button>
            <a href="{{ route('admin.commissions.index') }}" class="btn btn-sm btn-secondary mb-1">Reset</a>
        </form>

        <div class="d-flex align-items-center">
            <a href="{{ route('admin.commissions.tiers') }}" class="btn btn-sm btn-outline-info mr-2">
                <i class="fas fa-layer-group mr-1"></i>Tier Komisi
            </a>
            {{-- Bulk settle toolbar --}}
            <div id="bulk-toolbar" class="d-none">
                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalBulkSettle">
                    <i class="fas fa-check-double mr-1"></i>Settle <span id="selected-count">0</span> Komisi
                </button>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="40"><input type="checkbox" id="check-all"></th>
                        <th>Mitra</th>
                        <th>Produk</th>
                        <th>Booking</th>
                        <th class="text-right">Gross</th>
                        <th class="text-center">Rate</th>
                        <th class="text-right">Komisi</th>
                        <th class="text-right">Net Mitra</th>
                        <th class="text-center">Status</th>
                        <th>Settled At</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $c)
                    @php
                        $sc = ['pending'=>'warning','processed'=>'info','settled'=>'success','cancelled'=>'danger'];
                    @endphp
                    <tr>
                        <td>
                            @if(in_array($c->status, ['pending','processed']))
                            <input type="checkbox" class="comm-check" value="{{ $c->id }}">
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.mitra.index') }}" class="font-weight-medium">
                                {{ $c->business_name }}
                            </a>
                        </td>
                        <td>
                            <span class="text-sm">{{ $c->product_name_snapshot }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.bookings.show', $c->booking_id ?? 0) }}" class="text-xs">
                                {{ $c->booking_code }}
                            </a>
                            <br><small class="text-muted">{{ \Carbon\Carbon::parse($c->visit_date)->format('d M Y') }}</small>
                        </td>
                        <td class="text-right">Rp {{ number_format($c->gross_amount, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $c->commission_rate }}%</td>
                        <td class="text-right text-danger">
                            Rp {{ number_format($c->commission_amount, 0, ',', '.') }}
                        </td>
                        <td class="text-right text-success font-weight-bold">
                            Rp {{ number_format($c->net_amount, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $sc[$c->status] ?? 'secondary' }}">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>
                        <td class="text-xs text-muted">
                            {{ $c->settled_at ? \Carbon\Carbon::parse($c->settled_at)->format('d M Y') : '—' }}
                            @if($c->settlement_ref)
                                <br><code class="text-xs">{{ $c->settlement_ref }}</code>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.commissions.show', $c->id) }}"
                                class="btn btn-xs btn-outline-info" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(in_array($c->status, ['pending','processed']))
                            <button type="button" class="btn btn-xs btn-outline-success btn-settle"
                                data-id="{{ $c->id }}" title="Settle">
                                <i class="fas fa-check"></i>
                            </button>
                            @endif
                            @if($c->status !== 'settled')
                            <form action="{{ route('admin.commissions.cancel', $c->id) }}"
                                method="POST" class="d-inline form-cancel">
                                @csrf
                                <button class="btn btn-xs btn-outline-danger" title="Batalkan">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="fas fa-percentage fa-2x mb-2 d-block"></i>
                            Belum ada data komisi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($commissions->hasPages())
        <div class="card-footer">{{ $commissions->links() }}</div>
        @endif
    </div>
</div>
</section>

{{-- Modal Settle Single --}}
<div class="modal fade" id="modalSettle" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="form-settle" method="POST">
                @csrf
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white"><i class="fas fa-check mr-2"></i>Settle Komisi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label>Referensi Transfer <span class="text-danger">*</span></label>
                        <input type="text" name="settlement_ref" class="form-control"
                            placeholder="TRF-20260405-001" required>
                        <small class="text-muted">No. referensi transfer bank ke mitra.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check mr-1"></i>Settle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Bulk Settle --}}
<div class="modal fade" id="modalBulkSettle" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-bulk-settle" action="{{ route('admin.commissions.bulk-settle') }}" method="POST">
                @csrf
                <div id="bulk-ids-container"></div>
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-check-double mr-2"></i>Bulk Settle Komisi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Settle <strong id="bulk-count">0</strong> komisi sekaligus.</p>
                    <div class="form-group mb-0">
                        <label>Referensi Transfer <span class="text-danger">*</span></label>
                        <input type="text" name="settlement_ref" class="form-control"
                            placeholder="TRF-BULK-20260405" required>
                        <small class="text-muted">Satu referensi untuk semua komisi yang dipilih.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-double mr-1"></i>Settle Semua
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
$(function () {
    $('#filter-mitra').select2({ theme: 'bootstrap4', width: '200px' });

    // Check all
    const checkAll  = document.getElementById('check-all');
    const checks    = () => document.querySelectorAll('.comm-check');
    const bulkBar   = document.getElementById('bulk-toolbar');
    const countEl   = document.getElementById('selected-count');
    const bulkCount = document.getElementById('bulk-count');

    function updateBulk() {
        const n = document.querySelectorAll('.comm-check:checked').length;
        bulkBar.classList.toggle('d-none', n === 0);
        countEl.textContent = n;
        bulkCount.textContent = n;
    }

    checkAll?.addEventListener('change', function () {
        checks().forEach(c => c.checked = this.checked);
        updateBulk();
    });
    document.querySelectorAll('.comm-check').forEach(c => c.addEventListener('change', updateBulk));

    // Inject IDs ke bulk form
    $('#modalBulkSettle').on('show.bs.modal', function () {
        const container = document.getElementById('bulk-ids-container');
        container.innerHTML = '';
        document.querySelectorAll('.comm-check:checked').forEach(c => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'commission_ids[]';
            inp.value = c.value;
            container.appendChild(inp);
        });
    });

    // Settle single — set action URL ke modal
    document.querySelectorAll('.btn-settle').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            document.getElementById('form-settle').action = `/admin/commissions/${id}/settle`;
            $('#modalSettle').modal('show');
        });
    });

    // Cancel
    document.querySelectorAll('.form-cancel').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const f = this;
            Swal.fire({
                title: 'Batalkan komisi ini?', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Tidak', confirmButtonColor: '#dc3545',
            }).then(r => { if (r.isConfirmed) f.submit(); });
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
