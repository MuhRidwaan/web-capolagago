@extends('backend.main_backend')

@section('title', 'Role Data')

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
@endpush

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Role Data</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Role Data</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @include('backend.layouts.flash')

            <div class="card capolaga-user-card">
                <div class="card-header border-0">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                        <h3 class="card-title font-weight-normal">Role Data</h3>

                        <div class="capolaga-user-toolbar mt-3 mt-lg-0">
                            <form method="GET" action="{{ route('admin.roles.index') }}" class="capolaga-user-search">
                                <div class="input-group">
                                    <input type="text" name="q" class="form-control" placeholder="Search role..."
                                        value="{{ request('q') }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit" aria-label="Search role">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <button type="button" class="btn btn-success capolaga-action-btn" disabled>
                                <i class="far fa-file-excel mr-1"></i> Export
                            </button>

                            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary capolaga-action-btn">
                                <i class="fas fa-plus mr-1"></i> Tambah Role
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover capolaga-user-table">
                            <thead>
                                <tr>
                                    <th style="width: 72px;">No</th>
                                    <th>Role Name</th>
                                    <th>Permissions</th>
                                    <th style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr>
                                        <td>{{ $roles->firstItem() + $loop->index }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>
                                            <span class="badge badge-success">{{ $role->permissions_count }} Permissions</span>
                                        </td>
                                        <td>
                                            <div class="capolaga-action-group">
                                                <a href="{{ route('admin.roles.edit', $role) }}"
                                                    class="btn btn-warning btn-sm capolaga-icon-btn" title="Edit role">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                                    class="d-inline form-delete-role">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm capolaga-icon-btn" type="submit"
                                                        title="Hapus role">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted p-4">Belum ada data role.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer clearfix">
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script src="{{ asset('backend/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(function () {
    $('.form-delete-role').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Hapus role ini?',
            text: 'Role yang masih dipakai user tidak bisa dihapus.',
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
