@extends('backend.main_backend')

@section('title', 'Mitra Data')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Mitra Data</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Mitra Data</li>
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
                        <h3 class="card-title font-weight-normal">Partner Directory</h3>

                        <div class="capolaga-user-toolbar mt-3 mt-lg-0">
                            <form method="GET" action="{{ route('admin.mitra.index') }}" class="capolaga-user-search">
                                <div class="input-group">
                                    <input type="text" name="q" class="form-control" placeholder="Search mitra..."
                                        value="{{ request('q') }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit" aria-label="Search mitra">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <form method="GET" action="{{ route('admin.mitra.index') }}">
                                <input type="hidden" name="q" value="{{ request('q') }}">
                                <select name="status" class="form-control capolaga-action-btn" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" @selected(request('status') === $status)>
                                            {{ str($status)->headline() }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>

                            <a href="{{ route('admin.mitra.create') }}" class="btn btn-primary capolaga-action-btn">
                                <i class="fas fa-plus mr-1"></i> Add Mitra
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
                                    <th>Business</th>
                                    <th>Owner Account</th>
                                    <th>Status</th>
                                    <th>Subscription</th>
                                    <th>Products</th>
                                    <th style="width: 210px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mitras as $mitra)
                                    @php
                                        $badgeClass = match ($mitra->status) {
                                            'active' => 'success',
                                            'pending' => 'warning',
                                            'inactive' => 'secondary',
                                            default => 'danger',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $mitras->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="font-weight-bold">{{ $mitra->business_name }}</div>
                                            <div class="text-muted small">{{ $mitra->slug }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $mitra->user?->name }}</div>
                                            <div class="text-muted small">{{ $mitra->user?->email }}</div>
                                        </td>
                                        <td><span class="badge badge-{{ $badgeClass }}">{{ str($mitra->status)->headline() }}</span></td>
                                        <td>{{ str($mitra->subscription_type)->headline() }}</td>
                                        <td>{{ $mitra->products_count }}</td>
                                        <td>
                                            <div class="capolaga-action-group flex-wrap">
                                                <a href="{{ route('admin.mitra.edit', $mitra) }}"
                                                    class="btn btn-warning btn-sm capolaga-icon-btn" title="Edit mitra">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                @if ($mitra->status !== 'active')
                                                    <form action="{{ route('admin.mitra.status', $mitra) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="active">
                                                        <button class="btn btn-success btn-sm capolaga-icon-btn" type="submit" title="Activate">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($mitra->status !== 'suspended')
                                                    <form action="{{ route('admin.mitra.status', $mitra) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="suspended">
                                                        <button class="btn btn-secondary btn-sm capolaga-icon-btn" type="submit" title="Suspend">
                                                            <i class="fas fa-pause"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <form action="{{ route('admin.mitra.destroy', $mitra) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus mitra ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm capolaga-icon-btn" type="submit" title="Hapus mitra">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted p-4">Belum ada data mitra.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer clearfix">
                    {{ $mitras->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection

