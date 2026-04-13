@extends('backend.main_backend')
@section('title', 'Profil Saya')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Profil Saya</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Profil</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('backend.layouts.flash')

    <div class="row">
        {{-- Info Card --}}
        <div class="col-lg-4 mb-4">
            <div class="card card-outline card-primary text-center">
                <div class="card-body pt-4 pb-4">
                    <div class="d-flex justify-content-center mb-3">
                        <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;font-weight:700;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                    <h5 class="font-weight-bold mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    <div class="d-flex justify-content-center flex-wrap gap-1">
                        @foreach($user->roles as $role)
                            <span class="badge badge-success px-3 py-1">{{ $role->name }}</span>
                        @endforeach
                    </div>
                    <hr>
                    <div class="text-left text-sm">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">No. HP</span>
                            <span>{{ $user->phone ?: '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Bergabung</span>
                            <span>{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Login terakhir</span>
                            <span>{{ $user->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            {{-- Update Info --}}
            <div class="card card-outline card-primary mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-edit mr-2"></i>Informasi Akun</h3>
                </div>
                <form action="{{ route('admin.profile.update-info') }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="card-body">
                        <div class="form-group">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label>No. HP</label>
                            <input type="tel" name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $user->phone) }}"
                                placeholder="08xxxxxxxxxx" maxlength="20">
                            <small class="text-muted">Opsional. Digunakan untuk keperluan kontak.</small>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            {{-- Update Password --}}
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lock mr-2"></i>Ubah Password</h3>
                </div>
                <form action="{{ route('admin.profile.update-password') }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="card-body">
                        <div class="form-group">
                            <label>Password Saat Ini <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="current_password" id="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    placeholder="••••••••" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary toggle-pw" data-target="current_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            @error('current_password')<div class="text-danger text-sm mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="new_password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Min. 8 karakter" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary toggle-pw" data-target="new_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label>Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="confirm_password"
                                    class="form-control" placeholder="Ulangi password baru" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary toggle-pw" data-target="confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key mr-1"></i>Ubah Password
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
<script>
document.querySelectorAll('.toggle-pw').forEach(btn => {
    btn.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.target);
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});
</script>
@endpush
