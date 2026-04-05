@extends('backend.main_backend')

@section('title', 'Edit Role')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Role</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Role Data</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                    <h3 class="card-title mb-0">Form Edit Role</h3>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.roles.update', $role) }}" method="POST" id="edit-role-form" data-swal-managed="custom">
                        @csrf
                        @method('PUT')
                        @php
                            $groupedPermissions = $permissions->groupBy(function ($permission) {
                                return str_contains($permission->name, '_')
                                    ? str($permission->name)->before('_')->headline()
                                    : 'General';
                            });
                        @endphp

                        <div class="form-group">
                            <label class="capolaga-form-label">Role Information</label>
                            <div class="text-muted small">
                                Role: {{ $role->name }} |
                                Total permissions: {{ $role->permissions->count() }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Role Name <span class="capolaga-required">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $role->name) }}"
                                class="form-control capolaga-form-control @error('name') is-invalid @enderror"
                                placeholder="Enter role name" required>
                            <small class="form-text text-muted">Ubah nama role jika memang perlu disesuaikan.</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Permissions</label>
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                                <small class="form-text text-muted mb-2 mb-md-0">Pilih permission yang akan diberikan ke role ini.</small>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-link p-0 mr-3" id="select-all-permissions">Select all</button>
                                    <button type="button" class="btn btn-link p-0 text-secondary" id="clear-all-permissions">Clear all</button>
                                </div>
                            </div>
                            <div class="row">
                                @foreach ($groupedPermissions as $group => $groupPermissions)
                                    <div class="col-lg-4 col-md-6 mb-4">
                                        <div class="card h-100 capolaga-permission-card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h4 class="capolaga-permission-title mb-1">{{ $group }}</h4>
                                                        <p class="capolaga-permission-subtitle mb-0">{{ $groupPermissions->count() }} permission</p>
                                                    </div>
                                                    <span class="badge capolaga-permission-badge">{{ $groupPermissions->count() }}</span>
                                                </div>

                                                @foreach ($groupPermissions as $permission)
                                                    <label class="capolaga-permission-option" for="perm_{{ $permission->id }}">
                                                        <input type="checkbox" id="perm_{{ $permission->id }}" name="permissions[]"
                                                            value="{{ $permission->name }}"
                                                            {{ in_array($permission->name, old('permissions', $role->permissions->pluck('name')->all()), true) ? 'checked' : '' }}>
                                                        <span>{{ str($permission->name)->replace('_', ' ')->headline() }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('permissions')
                                <div class="text-danger text-sm">{{ $message }}</div>
                            @enderror
                            @error('permissions.*')
                                <div class="text-danger text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="capolaga-form-footer">
                            <button type="submit" class="btn btn-primary capolaga-action-btn" id="update-role-btn" disabled>Perbarui</button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary capolaga-action-btn">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <style>
        .capolaga-permission-card {
            border-color: #dbe5ef !important;
            border-radius: 0.8rem;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }

        .capolaga-permission-title {
            font-size: 1rem;
            font-weight: 700;
            color: #183247;
        }

        .capolaga-permission-subtitle {
            font-size: 0.82rem;
            color: #7b8a9a;
        }

        .capolaga-permission-badge {
            min-width: 2rem;
            padding: 0.35rem 0.55rem;
            border-radius: 999px;
            background-color: #eef6ff;
            color: #1f8fff;
            font-weight: 700;
        }

        .capolaga-permission-option {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            margin-bottom: 0.85rem;
            padding: 0.75rem 0.85rem;
            border: 1px solid #e4ebf2;
            border-radius: 0.7rem;
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .capolaga-permission-option:last-child {
            margin-bottom: 0;
        }

        .capolaga-permission-option:hover {
            background-color: #f8fbff;
            border-color: #bfdcff;
        }

        .capolaga-permission-option input {
            width: 1.05rem;
            height: 1.05rem;
            margin: 0;
            accent-color: #1f8fff;
        }

        .capolaga-permission-option span {
            font-size: 0.95rem;
            font-weight: 600;
            color: #213547;
            line-height: 1.35;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('backend/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('edit-role-form');
            const updateButton = document.getElementById('update-role-btn');
            const permissionCheckboxes = Array.from(form?.querySelectorAll('input[name="permissions[]"]') ?? []);
            const selectAllButton = document.getElementById('select-all-permissions');
            const clearAllButton = document.getElementById('clear-all-permissions');
            let isSubmitting = false;

            if (! form || ! updateButton) {
                return;
            }

            const requiredFields = Array.from(form.querySelectorAll('[required]'));
            const trackedFields = Array.from(form.elements).filter((field) => (
                field.name !== '' &&
                ! ['hidden', 'submit', 'button', 'reset', 'file'].includes(field.type)
            ));

            const getFieldValue = (field) => {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    return field.checked ? field.value : '';
                }

                return field.value.trim();
            };

            const initialValues = new Map(
                trackedFields.map((field, index) => [`${field.name}:${field.type}:${index}`, getFieldValue(field)])
            );

            const updateButtonState = () => {
                const allFilled = requiredFields.every((field) => field.value.trim() !== '');
                const hasChanges = trackedFields.some((field, index) => (
                    initialValues.get(`${field.name}:${field.type}:${index}`) !== getFieldValue(field)
                ));

                updateButton.disabled = isSubmitting || ! allFilled || ! hasChanges;
            };

            trackedFields.forEach((field) => {
                field.addEventListener('input', updateButtonState);
                field.addEventListener('change', updateButtonState);
            });

            selectAllButton?.addEventListener('click', function () {
                permissionCheckboxes.forEach((checkbox) => {
                    checkbox.checked = true;
                });

                updateButtonState();
            });

            clearAllButton?.addEventListener('click', function () {
                permissionCheckboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });

                updateButtonState();
            });

            window.addEventListener('pageshow', function () {
                isSubmitting = false;

                if (typeof Swal !== 'undefined') {
                    Swal.close();
                }

                updateButtonState();
            });

            form.addEventListener('submit', function (event) {
                if (isSubmitting) {
                    return;
                }

                event.preventDefault();

                if (typeof Swal === 'undefined') {
                    isSubmitting = true;
                    updateButtonState();
                    form.submit();
                    return;
                }

                Swal.fire({
                    title: 'Perbarui role?',
                    text: 'Perubahan data role akan langsung disimpan.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, perbarui',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    confirmButtonColor: '#1f8fff',
                    cancelButtonColor: '#6d7a86',
                    showClass: {
                        popup: 'capolaga-swal-show'
                    },
                    hideClass: {
                        popup: 'capolaga-swal-hide'
                    }
                }).then((result) => {
                    if (! result.isConfirmed) {
                        return;
                    }

                    isSubmitting = true;
                    updateButtonState();

                    Swal.fire({
                        title: 'Memperbarui...',
                        text: 'Mohon tunggu, data role sedang diproses.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    form.submit();
                });
            });

            updateButtonState();
        });
    </script>
@endpush
