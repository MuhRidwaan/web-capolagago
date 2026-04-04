@extends('backend.main_backend')

@section('title', 'User Data')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">User Data</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">User Data</li>
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
                        <h3 class="card-title font-weight-normal">User Data</h3>

                        <div class="capolaga-user-toolbar mt-3 mt-lg-0">
                            <form method="GET" action="{{ route('admin.users.index') }}" class="capolaga-user-search">
                                <div class="input-group">
                                    <input type="text" name="q" class="form-control" placeholder="Search user..."
                                        value="{{ request('q') }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit" aria-label="Search user">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <button type="button" class="btn btn-success capolaga-action-btn" disabled>
                                <i class="far fa-file-excel mr-1"></i> Export
                            </button>

                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary capolaga-action-btn">
                                <i class="fas fa-plus mr-1"></i> Add User
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
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th style="width: 140px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td>{{ $users->firstItem() + $loop->index }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @forelse ($user->roles as $role)
                                                <span class="badge badge-success">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-muted">-</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <div class="capolaga-action-group">
                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                    class="btn btn-warning btn-sm capolaga-icon-btn" title="Edit user">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm capolaga-icon-btn" type="submit"
                                                        title="Hapus user">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted p-4">Belum ada data user.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer clearfix">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection
