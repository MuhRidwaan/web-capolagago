<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CapolagaGo') }} — Login</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('backend/plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #0f1923;
        }

        /* ── Left panel ── */
        .panel-left {
            flex: 1;
            position: relative;
            display: none;
            overflow: hidden;
        }
        @media (min-width: 900px) { .panel-left { display: flex; } }

        .panel-left-bg {
            position: absolute; inset: 0;
            background: url('{{ asset('images/glamping.jpg') }}') center/cover no-repeat;
        }
        .panel-left-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(10,30,20,.82) 0%, rgba(16,53,40,.70) 60%, rgba(0,0,0,.45) 100%);
        }
        .panel-left-content {
            position: relative; z-index: 1;
            display: flex; flex-direction: column; justify-content: flex-end;
            padding: 3rem;
            color: #fff;
        }
        .panel-left-badge {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 999px;
            padding: .35rem .9rem;
            font-size: .75rem; font-weight: 600; letter-spacing: .08em;
            color: #a7f3d0;
            margin-bottom: 1.5rem;
            width: fit-content;
        }
        .panel-left-title {
            font-size: 2.4rem; font-weight: 700; line-height: 1.2;
            margin-bottom: .75rem;
        }
        .panel-left-sub {
            font-size: .95rem; color: rgba(255,255,255,.7); line-height: 1.6;
            max-width: 380px;
        }
        .panel-left-stats {
            display: flex; gap: 2rem; margin-top: 2rem;
        }
        .stat-item { }
        .stat-num { font-size: 1.5rem; font-weight: 700; color: #6ee7b7; }
        .stat-label { font-size: .75rem; color: rgba(255,255,255,.55); margin-top: .1rem; }

        /* ── Right panel ── */
        .panel-right {
            width: 100%; max-width: 480px;
            display: flex; flex-direction: column; justify-content: center;
            padding: 2.5rem 2rem;
            background: #fff;
        }
        @media (min-width: 900px) { .panel-right { padding: 3rem 3.5rem; } }

        .brand {
            display: flex; align-items: center; gap: .6rem;
            margin-bottom: 2.5rem;
        }
        .brand-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: linear-gradient(135deg, #059669, #10b981);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1rem;
        }
        .brand-name { font-size: 1.2rem; font-weight: 700; color: #0f172a; }
        .brand-sub { font-size: .7rem; color: #64748b; font-weight: 400; }

        .form-heading { font-size: 1.6rem; font-weight: 700; color: #0f172a; margin-bottom: .4rem; }
        .form-subheading { font-size: .9rem; color: #64748b; margin-bottom: 2rem; }

        .form-label {
            display: block; font-size: .8rem; font-weight: 600;
            color: #374151; margin-bottom: .4rem;
        }
        .input-wrap {
            position: relative; margin-bottom: 1.25rem;
        }
        .input-wrap .input-icon {
            position: absolute; left: .9rem; top: 50%; transform: translateY(-50%);
            color: #9ca3af; font-size: .85rem; pointer-events: none;
        }
        .input-wrap input {
            width: 100%; padding: .7rem .9rem .7rem 2.4rem;
            border: 1.5px solid #e5e7eb; border-radius: 10px;
            font-size: .9rem; color: #0f172a;
            outline: none; transition: border-color .2s, box-shadow .2s;
            background: #f9fafb;
        }
        .input-wrap input:focus {
            border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.12);
            background: #fff;
        }
        .input-wrap input.is-invalid { border-color: #ef4444; }
        .invalid-msg { font-size: .78rem; color: #ef4444; margin-top: -.8rem; margin-bottom: .8rem; }

        .toggle-pw {
            position: absolute; right: .9rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #9ca3af;
            font-size: .85rem; padding: 0;
        }
        .toggle-pw:hover { color: #374151; }

        .row-remember {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .remember-label {
            display: flex; align-items: center; gap: .45rem;
            font-size: .83rem; color: #374151; cursor: pointer;
        }
        .remember-label input[type=checkbox] { accent-color: #10b981; width: 15px; height: 15px; }

        .btn-login {
            width: 100%; padding: .8rem;
            background: linear-gradient(135deg, #059669, #10b981);
            color: #fff; font-size: .95rem; font-weight: 600;
            border: none; border-radius: 10px; cursor: pointer;
            transition: opacity .2s, transform .1s;
            letter-spacing: .01em;
        }
        .btn-login:hover { opacity: .92; }
        .btn-login:active { transform: scale(.99); }

        .divider {
            display: flex; align-items: center; gap: .75rem;
            margin: 1.5rem 0; color: #d1d5db; font-size: .8rem;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: #e5e7eb;
        }

        .back-link {
            display: flex; align-items: center; justify-content: center; gap: .4rem;
            font-size: .83rem; color: #64748b; text-decoration: none;
            transition: color .2s;
        }
        .back-link:hover { color: #059669; }

        .alert-error {
            background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px;
            padding: .75rem 1rem; margin-bottom: 1.25rem;
            font-size: .83rem; color: #b91c1c;
            display: flex; align-items: flex-start; gap: .5rem;
        }
    </style>
</head>
<body>

{{-- Left panel --}}
<div class="panel-left">
    <div class="panel-left-bg"></div>
    <div class="panel-left-overlay"></div>
    <div class="panel-left-content">
        <div class="panel-left-badge">
            <i class="fas fa-leaf"></i> Wisata Alam Capolaga
        </div>
        <h1 class="panel-left-title">Kelola Wisata<br>Lebih Mudah</h1>
        <p class="panel-left-sub">
            Platform manajemen booking, produk, dan laporan untuk Wisata Desa Capolaga.
        </p>
        <div class="panel-left-stats">
            <div class="stat-item">
                <div class="stat-num">100+</div>
                <div class="stat-label">Produk Wisata</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">24/7</div>
                <div class="stat-label">Booking Online</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">Real-time</div>
                <div class="stat-label">Laporan & Komisi</div>
            </div>
        </div>
    </div>
</div>

{{-- Right panel --}}
<div class="panel-right">
    <div class="brand">
        <div class="brand-icon"><i class="fas fa-mountain"></i></div>
        <div>
            <div class="brand-name">{{ config('app.name', 'CapolagaGo') }}</div>
            <div class="brand-sub">Admin Dashboard</div>
        </div>
    </div>

    <h2 class="form-heading">Selamat datang 👋</h2>
    <p class="form-subheading">Masuk ke akun kamu untuk melanjutkan</p>

    @if($errors->any())
    <div class="alert-error">
        <i class="fas fa-exclamation-circle mt-0.5"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <label class="form-label" for="email">Email</label>
        <div class="input-wrap">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" id="email" name="email"
                placeholder="nama@email.com"
                value="{{ old('email') }}"
                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                required autofocus>
        </div>
        @error('email')
            <div class="invalid-msg">{{ $message }}</div>
        @enderror

        <label class="form-label" for="password">Password</label>
        <div class="input-wrap">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="password" name="password"
                placeholder="••••••••"
                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                required>
            <button type="button" class="toggle-pw" onclick="togglePassword()">
                <i class="fas fa-eye" id="pw-icon"></i>
            </button>
        </div>
        @error('password')
            <div class="invalid-msg">{{ $message }}</div>
        @enderror

        <div class="row-remember">
            <label class="remember-label">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                Ingat saya
            </label>
        </div>

        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt mr-2"></i>Masuk
        </button>
    </form>

    <div class="divider">atau</div>

    <a href="{{ url('/') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Kembali ke halaman utama
    </a>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('pw-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
</body>
</html>
