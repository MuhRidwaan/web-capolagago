<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Capolaga') }} | Login</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('backend/plugins/fontawesome-free/css/all.min.css') }}">

    <style>
        :root {
            --page-bg: #eef3ef;
            --panel-bg: #f8faf8;
            --surface: #ffffff;
            --text: #24493a;
            --muted: #7f8d87;
            --line: #dbe4de;
            --accent: #2f7356;
            --accent-strong: #255c45;
            --danger: #c55454;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Plus Jakarta Sans", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(209, 235, 214, 0.85), transparent 22%),
                linear-gradient(135deg, #edf4ef 0%, #f4f7f4 100%);
        }

        a { color: inherit; }

        .auth-shell { min-height: 100vh; }

        .auth-frame {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(420px, 46%) minmax(0, 54%);
            overflow: hidden;
        }

        .panel-left {
            background: var(--panel-bg);
            padding: 42px 48px 28px;
            display: flex;
            flex-direction: column;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 54px;
            text-decoration: none;
        }

        .brand-badge {
            width: 58px; height: 58px;
            border-radius: 20px;
            display: grid; place-items: center;
            color: #fff;
            background: linear-gradient(135deg, #24694c, #1c523b);
            box-shadow: 0 14px 28px rgba(37, 92, 69, 0.22);
            font-size: 1.3rem;
        }

        .brand-copy {
            display: flex; flex-direction: column; line-height: 1.1;
        }

        .brand-copy strong {
            font-size: 1.45rem; font-weight: 800; letter-spacing: -0.03em;
        }

        .brand-copy span {
            color: #ce8650; font-size: 0.9rem; letter-spacing: 0.2em; text-transform: uppercase;
        }

        .form-wrap {
            width: min(100%, 386px);
            margin: auto;
        }

        .heading {
            margin: 0 0 8px;
            font-size: clamp(2rem, 4vw, 2.65rem);
            line-height: 1.08;
            letter-spacing: -0.04em;
        }

        .subheading {
            margin: 0 0 26px;
            color: #68766f;
            font-size: 1.01rem;
        }

        .switcher {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 6px;
            margin-bottom: 28px;
            background: linear-gradient(135deg, #d8f0d9, #c6e9c7);
            border-radius: 999px;
        }

        .switcher-link {
            padding: 13px 18px;
            border-radius: 999px;
            text-align: center;
            text-decoration: none;
            font-weight: 700;
            color: #2d684f;
            transition: 0.2s ease;
        }

        .switcher-link.active {
            background: linear-gradient(135deg, #2f7356, #295e47);
            color: #fff;
            box-shadow: 0 10px 24px rgba(37, 92, 69, 0.22);
        }

        .feedback {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 16px;
            font-size: 0.92rem;
            background: rgba(197, 84, 84, 0.09);
            color: var(--danger);
            border: 1px solid rgba(197, 84, 84, 0.14);
        }

        .field { margin-bottom: 16px; }

        .input-shell {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 18px;
        }

        .field-icon {
            position: absolute;
            left: 22px; top: 50%;
            transform: translateY(-50%);
            color: #4d9c77;
            font-size: 1rem;
        }

        .floating-label {
            position: absolute;
            left: 54px; top: 13px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #a2ada8;
        }

        .input-shell input {
            width: 100%;
            border: 0;
            background: transparent;
            outline: none;
            color: var(--text);
            font-size: 1rem;
            padding: 32px 56px 16px 54px;
        }

        .input-shell input::placeholder { color: #bec6c1; }

        .input-shell:focus-within {
            border-color: rgba(47, 115, 86, 0.35);
        }

        .password-toggle {
            position: absolute;
            right: 18px; top: 50%;
            transform: translateY(-50%);
            width: 36px; height: 36px;
            border: 0; border-radius: 999px;
            background: transparent;
            color: #9aa5a0;
            cursor: pointer;
        }

        .password-toggle:hover {
            color: var(--accent);
            background: rgba(47, 115, 86, 0.08);
        }

        .row-remember {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 8px 0 24px;
            font-size: 0.94rem;
            color: #5d6b64;
        }

        .remember-label {
            display: flex; align-items: center; gap: 10px; cursor: pointer;
        }

        .remember-label input {
            width: 17px; height: 17px;
            accent-color: var(--accent);
        }

        .submit-btn {
            width: 100%;
            border: 0;
            border-radius: 18px;
            padding: 18px 22px;
            background: linear-gradient(135deg, #317354, #295e47);
            color: #fff;
            font-size: 1.05rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 16px 30px rgba(37, 92, 69, 0.25);
            transition: filter .2s, transform .1s;
        }

        .submit-btn:hover { filter: brightness(1.03); transform: translateY(-1px); }

        .footer {
            margin-top: auto;
            padding-top: 30px;
            text-align: center;
            color: #a0aba5;
            font-size: 0.86rem;
        }

        .footer a { color: var(--accent); font-weight: 600; text-decoration: none; }
        .footer a:hover { color: var(--accent-strong); }

        .panel-right {
            position: relative;
            padding: 48px 46px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background:
                linear-gradient(180deg, rgba(58, 118, 88, 0.54), rgba(48, 87, 51, 0.4)),
                url('{{ asset('images/glamping.jpg') }}') center center / cover no-repeat;
            color: #f7fbf8;
        }

        .panel-right::after {
            content: "";
            position: absolute; inset: 0;
            background: radial-gradient(circle at center, rgba(255,255,255,0.06), transparent 42%);
            pointer-events: none;
        }

        .hero-top, .hero-content, .hero-cards { position: relative; z-index: 1; }

        .hero-chip {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 16px;
            border: 1px solid rgba(255,255,255,0.34);
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            font-size: 0.92rem; font-weight: 600;
            backdrop-filter: blur(4px);
        }

        .hero-overline {
            margin-bottom: 16px;
            font-size: 1rem; letter-spacing: 0.22em; text-transform: uppercase;
            color: rgba(255,255,255,0.82);
        }

        .hero-title {
            max-width: 520px; margin: 0;
            font-size: clamp(2.4rem, 5vw, 4.1rem);
            line-height: 1.08; letter-spacing: -0.05em;
        }

        .hero-text {
            max-width: 520px; margin: 22px 0 0;
            color: rgba(247,251,248,0.82);
            font-size: 1.05rem; line-height: 1.7;
        }

        .hero-cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .hero-card {
            min-height: 132px; padding: 24px 20px;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center; text-align: center;
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.24);
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(8px);
        }

        .hero-card i { margin-bottom: 16px; font-size: 1.35rem; }
        .hero-card strong { font-size: 1rem; }
        .hero-card span { margin-top: 8px; color: rgba(247,251,248,0.78); font-size: 0.82rem; }

        @media (max-width: 1100px) {
            .auth-frame { grid-template-columns: 1fr; }
            .panel-right { min-height: 480px; }
            .footer { margin-top: 32px; }
        }

        @media (max-width: 640px) {
            .panel-left, .panel-right { padding: 28px 20px; }
            .brand { margin-bottom: 36px; }
            .form-wrap { width: 100%; }
            .hero-title { font-size: 2.25rem; }
            .hero-cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="auth-frame">
        <section class="panel-left">
            <a class="brand" href="{{ url('/') }}">
                <span class="brand-badge"><i class="fas fa-map-marker-alt"></i></span>
                <span class="brand-copy">
                    <strong>Capolaga</strong>
                    <span>Bandung</span>
                </span>
            </a>

            <div class="form-wrap">
                <h1 class="heading">Selamat Datang</h1>
                <p class="subheading">Masuk ke akun kamu untuk melanjutkan perjalanan.</p>

                <div class="switcher">
                    <a class="switcher-link active" href="{{ route('login') }}">Masuk</a>
                    <a class="switcher-link" href="{{ route('register') }}">Daftar</a>
                </div>

                @if($errors->any())
                    <div class="feedback">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="field">
                        <div class="input-shell">
                            <i class="fas fa-envelope field-icon"></i>
                            <div class="floating-label">Email</div>
                            <input type="email" name="email" value="{{ old('email') }}"
                                placeholder="nama@email.com" autocomplete="email" required autofocus>
                        </div>
                    </div>

                    <div class="field">
                        <div class="input-shell">
                            <i class="fas fa-lock field-icon"></i>
                            <div class="floating-label">Kata Sandi</div>
                            <input id="password" type="password" name="password"
                                placeholder="Masukkan password" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" data-target="password" aria-label="Tampilkan password">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row-remember">
                        <label class="remember-label">
                            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                            Ingat saya
                        </label>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </button>
                </form>
            </div>

            <div class="footer">
                Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
            </div>
        </section>

        <aside class="panel-right">
            <div class="hero-top">
                <div class="hero-chip">
                    <i class="fas fa-mountain"></i>
                    Wisata Capolaga
                </div>
            </div>

            <div class="hero-content">
                <div class="hero-overline">Bandung, Jawa Barat</div>
                <h2 class="hero-title">Jelajahi Alam, Petualangan, dan Relaksasi Khas Capolaga</h2>
                <p class="hero-text">Dari camping ground yang rindang sampai air panas alami, semuanya siap jadi bagian dari perjalanan liburanmu berikutnya.</p>
            </div>

            <div class="hero-cards">
                <div class="hero-card">
                    <i class="fas fa-tree"></i>
                    <strong>Wisata Alam</strong>
                    <span>Udara segar pegunungan</span>
                </div>
                <div class="hero-card">
                    <i class="fas fa-water"></i>
                    <strong>Petualangan</strong>
                    <span>Aktivitas outdoor seru</span>
                </div>
                <div class="hero-card">
                    <i class="fas fa-fire"></i>
                    <strong>Camping</strong>
                    <span>Malam hangat & nyaman</span>
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
    document.querySelectorAll('.password-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.dataset.target);
            var icon = button.querySelector('i');
            if (!input || !icon) return;
            var isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');
            icon.classList.toggle('fa-eye', isPassword);
            icon.classList.toggle('fa-eye-slash', !isPassword);
        });
    });
</script>
</body>
</html>
