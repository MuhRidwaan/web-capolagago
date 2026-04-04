@extends('backend.main_backend')

@section('title', 'Activity Tags')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Activity Tags</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Activity Tags</li>
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
                        <h3 class="card-title font-weight-normal">Tag List</h3>

                        <div class="capolaga-user-toolbar mt-3 mt-lg-0">
                            <form method="GET" action="{{ route('admin.activity-tags.index') }}" class="capolaga-user-search">
                                <div class="input-group">
                                    <input type="text" name="q" class="form-control" placeholder="Search tag..."
                                        value="{{ request('q') }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <a href="{{ route('admin.activity-tags.create') }}" class="btn btn-primary capolaga-action-btn">
                                <i class="fas fa-plus mr-1"></i> Add Tag
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover capolaga-user-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Group</th>
                                    <th>Products</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tags as $tag)
                                    <tr>
                                        <td>{{ $tags->firstItem() + $loop->index }}</td>
                                        <td>{{ $tag->name }}</td>
                                        <td>{{ str($tag->group_name)->headline() }}</td>
                                        <td>{{ $tag->products_count }}</td>
                                        <td>
                                            <div class="capolaga-action-group">
                                                <a href="{{ route('admin.activity-tags.edit', $tag) }}"
                                                    class="btn btn-warning btn-sm capolaga-icon-btn">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.activity-tags.destroy', $tag) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus tag ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm capolaga-icon-btn" type="submit">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted p-4">Belum ada tag aktivitas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer clearfix">
                    {{ $tags->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection


