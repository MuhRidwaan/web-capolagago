@extends('backend.main_backend')

@section('title', 'Tambah User')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Tambah User</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Data</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @include('backend.layouts.flash')

            <div class="card capolaga-form-card">
                <div class="card-header capolaga-form-header">
                    <h3 class="card-title mb-0">Form Tambah User</h3>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST" id="create-user-form">
                        @csrf

                        <div class="form-group">
                            <label class="capolaga-form-label">Name <span class="capolaga-required">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                class="form-control capolaga-form-control @error('name') is-invalid @enderror"
                                placeholder="Enter name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Email <span class="capolaga-required">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="form-control capolaga-form-control @error('email') is-invalid @enderror"
                                placeholder="Enter email" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Password <span class="capolaga-required">*</span></label>
                            <input type="password" name="password"
                                class="form-control capolaga-form-control @error('password') is-invalid @enderror"
                                placeholder="Password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Password Confirmation <span class="capolaga-required">*</span></label>
                            <input type="password" name="password_confirmation"
                                class="form-control capolaga-form-control @error('password') is-invalid @enderror"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Role <span class="capolaga-required">*</span></label>
                            <select name="roles[]" class="form-control capolaga-form-control @error('roles') is-invalid @enderror @error('roles.*') is-invalid @enderror" required>
                                <option value="">-- Select role --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}"
                                        {{ in_array($role->name, old('roles', []), true) ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('roles')
                                <div class="text-danger text-sm">{{ $message }}</div>
                            @enderror
                            @error('roles.*')
                                <div class="text-danger text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="capolaga-form-footer">
                            <button type="submit" class="btn btn-primary capolaga-action-btn" id="save-user-btn" disabled>Simpan</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary capolaga-action-btn">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('create-user-form');
            const saveButton = document.getElementById('save-user-btn');

            if (! form || ! saveButton) {
                return;
            }

            const requiredFields = Array.from(form.querySelectorAll('[required]'));

            const isFieldFilled = (field) => {
                if (field.tagName === 'SELECT') {
                    return field.value.trim() !== '';
                }

                return field.value.trim() !== '';
            };

            const updateSaveState = () => {
                const allFilled = requiredFields.every(isFieldFilled);
                saveButton.disabled = ! allFilled;
            };

            requiredFields.forEach((field) => {
                field.addEventListener('input', updateSaveState);
                field.addEventListener('change', updateSaveState);
            });

            updateSaveState();
        });
    </script>
@endpush
