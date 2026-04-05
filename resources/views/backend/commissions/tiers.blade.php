@extends('backend.main_backend')
@section('title', 'Tier Komisi')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Tier Komisi</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.commissions.index') }}">Komisi</a></li>
                    <li class="breadcrumb-item active">Tier</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    <div class="row">
        {{-- Daftar Tier --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-layer-group mr-2"></i>Daftar Tier</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama Tier</th>
                                <th class="text-right">Min. Omzet/Bulan</th>
                                <th class="text-center">Rate Komisi</th>
                                <th class="text-center">Diskon Subs.</th>
                                <th>Deskripsi</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tiers as $tier)
                            <tr>
                                <td><strong>{{ $tier->name }}</strong></td>
                                <td class="text-right">
                                    Rp {{ number_format($tier->min_monthly_revenue, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $tier->commission_rate }}%</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success">{{ $tier->subscription_discount }}%</span>
                                </td>
                                <td class="text-muted text-sm">{{ $tier->description ?? '—' }}</td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-xs btn-outline-primary btn-edit-tier"
                                        data-id="{{ $tier->id }}"
                                        data-name="{{ $tier->name }}"
                                        data-revenue="{{ $tier->min_monthly_revenue }}"
                                        data-rate="{{ $tier->commission_rate }}"
                                        data-discount="{{ $tier->subscription_discount }}"
                                        data-desc="{{ $tier->description }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.commissions.tiers.destroy', $tier->id) }}"
                                        method="POST" class="d-inline form-delete-tier">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Belum ada tier.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Form Tambah / Edit --}}
        <div class="col-lg-4">
            <div class="card card-outline card-primary" id="tier-form-card">
                <div class="card-header">
                    <h3 class="card-title" id="tier-form-title">
                        <i class="fas fa-plus mr-2"></i>Tambah Tier
                    </h3>
                </div>
                <form id="tier-form" action="{{ route('admin.commissions.tiers.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="tier-method" value="POST">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Nama Tier <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="tier-name" class="form-control"
                                placeholder="Gold" required>
                        </div>
                        <div class="form-group">
                            <label>Min. Omzet/Bulan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                <input type="number" name="min_monthly_revenue" id="tier-revenue"
                                    class="form-control" min="0" step="1000000" placeholder="20000000" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Rate Komisi (%) <span class="text-danger">*</span></label>
                                    <input type="number" name="commission_rate" id="tier-rate"
                                        class="form-control" min="0" max="100" step="0.5" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Diskon Subs. (%)</label>
                                    <input type="number" name="subscription_discount" id="tier-discount"
                                        class="form-control" min="0" max="100" step="0.5" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>Deskripsi</label>
                            <input type="text" name="description" id="tier-desc" class="form-control"
                                placeholder="Omzet Rp 20–50 juta/bulan">
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary btn-sm d-none" id="btn-cancel-edit">
                            Batal Edit
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm ml-auto">
                            <i class="fas fa-save mr-1"></i><span id="tier-submit-label">Tambah Tier</span>
                        </button>
                    </div>
                </form>
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
    // Edit tier — isi form
    document.querySelectorAll('.btn-edit-tier').forEach(btn => {
        btn.addEventListener('click', function () {
            const d = this.dataset;
            document.getElementById('tier-form').action = `/admin/commissions/tiers/${d.id}`;
            document.getElementById('tier-method').value = 'PUT';
            document.getElementById('tier-name').value = d.name;
            document.getElementById('tier-revenue').value = d.revenue;
            document.getElementById('tier-rate').value = d.rate;
            document.getElementById('tier-discount').value = d.discount;
            document.getElementById('tier-desc').value = d.desc;
            document.getElementById('tier-form-title').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Tier: ' + d.name;
            document.getElementById('tier-submit-label').textContent = 'Simpan Perubahan';
            document.getElementById('btn-cancel-edit').classList.remove('d-none');
            document.getElementById('tier-form-card').scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Batal edit
    document.getElementById('btn-cancel-edit').addEventListener('click', function () {
        document.getElementById('tier-form').action = '{{ route('admin.commissions.tiers.store') }}';
        document.getElementById('tier-method').value = 'POST';
        document.getElementById('tier-form').reset();
        document.getElementById('tier-form-title').innerHTML = '<i class="fas fa-plus mr-2"></i>Tambah Tier';
        document.getElementById('tier-submit-label').textContent = 'Tambah Tier';
        this.classList.add('d-none');
    });

    // Hapus tier
    document.querySelectorAll('.form-delete-tier').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const f = this;
            Swal.fire({
                title: 'Hapus tier ini?', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal', confirmButtonColor: '#dc3545',
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
