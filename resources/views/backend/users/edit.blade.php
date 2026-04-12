@extends('backend.main_backend')

@section('title', 'Edit User')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit User</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Data</a></li>
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
                    <h3 class="card-title mb-0">Edit User Form</h3>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST" id="edit-user-form" data-swal-managed="custom">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="capolaga-form-label">Account Information</label>
                            <div class="text-muted small">
                                Created at: {{ $user->created_at?->format('d M Y H:i') ?? '-' }} |
                                Last updated: {{ $user->updated_at?->format('d M Y H:i') ?? '-' }} |
                                Status: {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Name <span class="capolaga-required">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="form-control capolaga-form-control @error('name') is-invalid @enderror"
                                placeholder="Enter name" required>
                            <small class="form-text text-muted">Nama lengkap pengguna yang akan ditampilkan di sistem.</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Email <span class="capolaga-required">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="form-control capolaga-form-control @error('email') is-invalid @enderror"
                                placeholder="Enter email" required>
                            <small class="form-text text-muted">Email ini digunakan sebagai identitas login pengguna.</small>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Role <span class="capolaga-required">*</span></label>
                            <select name="roles[]" class="form-control capolaga-form-control @error('roles') is-invalid @enderror @error('roles.*') is-invalid @enderror" required>
                                <option value="">-- Select role --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}"
                                        {{ in_array($role->name, old('roles', $user->roles->pluck('name')->all()), true) ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Role menentukan hak akses dan menu yang bisa digunakan user.</small>
                            @error('roles')
                                <div class="text-danger text-sm">{{ $message }}</div>
                            @enderror
                            @error('roles.*')
                                <div class="text-danger text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Password</label>
                            <input type="password" name="password"
                                class="form-control capolaga-form-control @error('password') is-invalid @enderror"
                                placeholder="Leave blank if you do not want to change it">
                            <small class="form-text text-muted">Isi hanya jika ingin mengganti password user. Minimal 8 karakter.</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="capolaga-form-label">Password Confirmation</label>
                            <input type="password" name="password_confirmation"
                                class="form-control capolaga-form-control"
                                placeholder="Confirm new password if you want to change it">
                            <small class="form-text text-muted">Ulangi password baru untuk memastikan tidak ada salah ketik.</small>
                        </div>

                        <div class="capolaga-form-footer">
                            <button type="submit" class="btn btn-primary capolaga-action-btn" id="update-user-btn" disabled>Perbarui</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary capolaga-action-btn">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        @keyframes capolagaSwalZoomIn {
            from {
                opacity: 0;
                transform: translateY(-14px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes capolagaSwalZoomOut {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            to {
                opacity: 0;
                transform: translateY(-10px) scale(0.98);
            }
        }

        .capolaga-swal-show {
            animation: capolagaSwalZoomIn 0.22s ease-out;
        }

        .capolaga-swal-hide {
            animation: capolagaSwalZoomOut 0.18s ease-in forwards;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('edit-user-form');
            const updateButton = document.getElementById('update-user-btn');
            let isSubmitting = false;

            if (! form || ! updateButton) {
                return;
            }

            const requiredFields = Array.from(form.querySelectorAll('[required]'));
            const trackedFields = Array.from(form.elements).filter((field) => (
                field.name !== '' &&
                ! ['hidden', 'submit', 'button', 'reset', 'file'].includes(field.type)
            ));

            const isFieldFilled = (field) => field.value.trim() !== '';

            const getFieldValue = (field) => {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    return field.checked ? field.value : '';
                }

                if (field.tagName === 'SELECT' && field.multiple) {
                    return Array.from(field.selectedOptions).map((option) => option.value).join('|');
                }

                return field.value.trim();
            };

            const initialValues = new Map(
                trackedFields.map((field) => [`${field.name}:${field.type}`, getFieldValue(field)])
            );

            const updateButtonState = () => {
                const allFilled = requiredFields.every(isFieldFilled);
                const hasChanges = trackedFields.some((field) => (
                    initialValues.get(`${field.name}:${field.type}`) !== getFieldValue(field)
                ));

                updateButton.disabled = isSubmitting || ! allFilled || ! hasChanges;
            };

            trackedFields.forEach((field) => {
                field.addEventListener('input', updateButtonState);
                field.addEventListener('change', updateButtonState);
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
                    title: 'Perbarui user?',
                    text: 'Perubahan data user akan langsung disimpan.',
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
                        text: 'Mohon tunggu, data user sedang diproses.',
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
