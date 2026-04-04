@extends('backend.main_backend')
@section('title', 'Metode Pembayaran')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Metode Pembayaran</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Metode Pembayaran</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            Drag baris untuk mengubah urutan tampil di halaman checkout.
        </p>
        <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i>Tambah Metode
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0" id="methods-table">
                <thead class="thead-light">
                    <tr>
                        <th width="30" class="text-center text-muted"><i class="fas fa-grip-vertical"></i></th>
                        <th width="50">Logo</th>
                        <th>Nama</th>
                        <th>Kode</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Provider</th>
                        <th class="text-right">Fee Flat</th>
                        <th class="text-right">Fee %</th>
                        <th class="text-right">Min</th>
                        <th class="text-right">Max</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="sortable-body">
                    @foreach($methods as $m)
                    @php
                        $typeColors = ['va'=>'info','ewallet'=>'success','qris'=>'primary','cc'=>'warning','cstore'=>'secondary','manual'=>'dark'];
                        $providerColors = ['midtrans'=>'danger','xendit'=>'primary','manual'=>'secondary'];
                    @endphp
                    <tr data-id="{{ $m->id }}" class="{{ $m->is_active ? '' : 'table-secondary text-muted' }}">
                        <td class="text-center drag-handle" style="cursor:grab">
                            <i class="fas fa-grip-vertical text-muted"></i>
                        </td>
                        <td>
                            @if($m->logo_path)
                                <img src="{{ Storage::url($m->logo_path) }}" alt="{{ $m->name }}"
                                    style="height:24px; object-fit:contain">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="font-weight-medium">{{ $m->name }}</td>
                        <td><code>{{ $m->code }}</code></td>
                        <td class="text-center">
                            <span class="badge badge-{{ $typeColors[$m->type] ?? 'secondary' }}">
                                {{ $types[$m->type] ?? $m->type }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $providerColors[$m->provider] ?? 'secondary' }}">
                                {{ ucfirst($m->provider) }}
                            </span>
                        </td>
                        <td class="text-right">
                            {{ $m->fee_flat > 0 ? 'Rp '.number_format($m->fee_flat, 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-right">
                            {{ $m->fee_percent > 0 ? number_format($m->fee_percent, 2).'%' : '—' }}
                        </td>
                        <td class="text-right text-xs">Rp {{ number_format($m->min_amount, 0, ',', '.') }}</td>
                        <td class="text-right text-xs">
                            {{ $m->max_amount ? 'Rp '.number_format($m->max_amount, 0, ',', '.') : '∞' }}
                        </td>
                        <td class="text-center">
                            <form action="{{ route('admin.payment-methods.toggle', $m->id) }}"
                                method="POST" class="d-inline form-toggle">
                                @csrf
                                <button type="submit"
                                    class="btn btn-xs {{ $m->is_active ? 'btn-success' : 'btn-outline-secondary' }}"
                                    title="{{ $m->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                                    <i class="fas fa-{{ $m->is_active ? 'check' : 'times' }}"></i>
                                    {{ $m->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.payment-methods.edit', $m->id) }}"
                                class="btn btn-xs btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.payment-methods.destroy', $m->id) }}"
                                method="POST" class="d-inline form-delete">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('backend/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('backend/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<script>
$(function () {
    // Drag & drop reorder
    $('#sortable-body').sortable({
        handle: '.drag-handle',
        axis: 'y',
        update: function () {
            const orders = [];
            $('#sortable-body tr').each(function (i) {
                orders.push({ id: $(this).data('id'), sort_order: i + 1 });
            });

            $.ajax({
                url: '{{ route('admin.payment-methods.reorder') }}',
                method: 'POST',
                data: JSON.stringify({ orders }),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: () => Swal.fire({
                    icon: 'success', title: 'Urutan disimpan',
                    timer: 1500, showConfirmButton: false,
                }),
                error: () => Swal.fire({ icon: 'error', title: 'Gagal menyimpan urutan.' }),
            });
        },
    });

    // Toggle aktif/nonaktif
    $('.form-toggle').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const isActive = $(form).find('button').hasClass('btn-success');
        Swal.fire({
            title: isActive ? 'Nonaktifkan metode ini?' : 'Aktifkan metode ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal',
            confirmButtonColor: isActive ? '#6c757d' : '#28a745',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });

    // Hapus
    $('.form-delete').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Hapus metode ini?',
            text: 'Metode yang sudah digunakan dalam transaksi tidak bisa dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
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
