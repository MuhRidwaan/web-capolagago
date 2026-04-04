@extends('backend.main_backend')

@section('title', 'Pengaturan Email')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Pengaturan Email</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Pengaturan Email</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('backend.layouts.flash')

        <div class="row">
            {{-- Form Konfigurasi --}}
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-envelope mr-2"></i>Konfigurasi SMTP</h3>
                    </div>

                    <form action="{{ route('admin.settings.mail.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">

                            {{-- Driver --}}
                            <div class="form-group">
                                <label>Driver / Mailer</label>
                                <select name="mail_mailer" id="mail_mailer"
                                    class="form-control @error('mail_mailer') is-invalid @enderror">
                                    <option value="smtp"     {{ ($settings['mail_mailer']->value ?? 'log') === 'smtp'     ? 'selected' : '' }}>SMTP</option>
                                    <option value="log"      {{ ($settings['mail_mailer']->value ?? 'log') === 'log'      ? 'selected' : '' }}>Log (Development)</option>
                                    <option value="sendmail" {{ ($settings['mail_mailer']->value ?? 'log') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                </select>
                                <small class="text-muted">Gunakan <strong>Log</strong> saat development — email tidak dikirim, hanya dicatat di log.</small>
                                @error('mail_mailer') <div class="text-danger text-sm">{{ $message }}</div> @enderror
                            </div>

                            {{-- SMTP Fields (tampil/sembunyi berdasarkan driver) --}}
                            <div id="smtp-fields">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>SMTP Host</label>
                                            <input type="text" name="mail_host"
                                                class="form-control @error('mail_host') is-invalid @enderror"
                                                value="{{ old('mail_host', $settings['mail_host']->value ?? '') }}"
                                                placeholder="smtp.gmail.com">
                                            @error('mail_host') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Port</label>
                                            <input type="number" name="mail_port"
                                                class="form-control @error('mail_port') is-invalid @enderror"
                                                value="{{ old('mail_port', $settings['mail_port']->value ?? '587') }}"
                                                placeholder="587">
                                            @error('mail_port') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Enkripsi</label>
                                    <select name="mail_encryption" class="form-control">
                                        @foreach(['tls' => 'TLS (Port 587)', 'ssl' => 'SSL (Port 465)', 'starttls' => 'STARTTLS', '' => 'Tidak Ada'] as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ ($settings['mail_encryption']->value ?? 'tls') === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Username / Email Pengirim</label>
                                    <input type="text" name="mail_username"
                                        class="form-control @error('mail_username') is-invalid @enderror"
                                        value="{{ old('mail_username', $settings['mail_username']->value ?? '') }}"
                                        placeholder="noreply@capolaga.com">
                                    @error('mail_username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label>
                                        Password / App Password
                                        <span class="badge badge-secondary ml-1">Secret</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" id="mail_password" name="mail_password"
                                            class="form-control @error('mail_password') is-invalid @enderror"
                                            placeholder="{{ filled($settings['mail_password']->value ?? null) ? '••••••••••••' : 'App password Gmail / SMTP password' }}"
                                            autocomplete="new-password">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                                data-target="mail_password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password yang tersimpan.</small>
                                    @error('mail_password') <div class="text-danger text-sm">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <hr>

                            {{-- From --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>From Address</label>
                                        <input type="email" name="mail_from_address"
                                            class="form-control @error('mail_from_address') is-invalid @enderror"
                                            value="{{ old('mail_from_address', $settings['mail_from_address']->value ?? '') }}"
                                            placeholder="noreply@capolaga.com">
                                        @error('mail_from_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>From Name</label>
                                        <input type="text" name="mail_from_name"
                                            class="form-control @error('mail_from_name') is-invalid @enderror"
                                            value="{{ old('mail_from_name', $settings['mail_from_name']->value ?? config('app.name')) }}"
                                            placeholder="Capolaga Adventure">
                                        @error('mail_from_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Form Test Email --}}
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-paper-plane mr-2"></i>Kirim Email Test</h3>
                    </div>
                    <form action="{{ route('admin.settings.mail.test') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <p class="text-muted">
                                Kirim email konfirmasi booking dummy ke alamat tertentu untuk memastikan konfigurasi SMTP berjalan dengan benar.
                            </p>
                            <div class="input-group">
                                <input type="email" name="test_email"
                                    class="form-control @error('test_email') is-invalid @enderror"
                                    placeholder="email@contoh.com"
                                    value="{{ old('test_email') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane mr-1"></i> Kirim Test
                                    </button>
                                </div>
                                @error('test_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Panel Info --}}
            <div class="col-lg-4">

                {{-- Status --}}
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Status Konfigurasi</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">Driver</td>
                                <td>
                                    @php $mailer = $settings['mail_mailer']->value ?? 'log'; @endphp
                                    @if($mailer === 'smtp')
                                        <span class="badge badge-success">SMTP</span>
                                    @elseif($mailer === 'log')
                                        <span class="badge badge-warning">Log (Dev)</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $mailer }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Host</td>
                                <td><code>{{ $settings['mail_host']->value ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Port</td>
                                <td>{{ $settings['mail_port']->value ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Enkripsi</td>
                                <td>{{ strtoupper($settings['mail_encryption']->value ?? '-') ?: 'Tidak Ada' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Password</td>
                                <td>
                                    @if(filled($settings['mail_password']->value ?? null))
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Tersimpan</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Belum diisi</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">From</td>
                                <td><small>{{ $settings['mail_from_address']->value ?? '-' }}</small></td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Tips --}}
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-lightbulb mr-2"></i>Tips Konfigurasi</h3>
                    </div>
                    <div class="card-body p-3" style="font-size:0.85rem; line-height:1.8">
                        <p class="font-weight-bold mb-1">Gmail (Rekomendasi):</p>
                        <ul class="pl-3 mb-2">
                            <li>Host: <code>smtp.gmail.com</code></li>
                            <li>Port: <code>587</code>, Enkripsi: <code>TLS</code></li>
                            <li>Aktifkan <strong>2FA</strong> di akun Google</li>
                            <li>Buat <strong>App Password</strong> di Google Account → Security</li>
                            <li>Gunakan App Password sebagai password SMTP</li>
                        </ul>
                        <p class="font-weight-bold mb-1">Mailtrap (Testing):</p>
                        <ul class="pl-3 mb-0">
                            <li>Host: <code>sandbox.smtp.mailtrap.io</code></li>
                            <li>Port: <code>2525</code></li>
                            <li>Cek inbox di <a href="https://mailtrap.io" target="_blank">mailtrap.io</a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Toggle show/hide password
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const icon  = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Sembunyikan SMTP fields jika driver bukan smtp
    const mailerSelect = document.getElementById('mail_mailer');
    const smtpFields   = document.getElementById('smtp-fields');

    function toggleSmtpFields() {
        smtpFields.style.display = mailerSelect.value === 'smtp' ? 'block' : 'none';
    }

    mailerSelect.addEventListener('change', toggleSmtpFields);
    toggleSmtpFields(); // init
</script>
@endpush
