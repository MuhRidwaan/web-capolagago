@extends('backend.main_backend')

@section('title', 'Pengaturan Pembayaran')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Pengaturan Pembayaran</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Pengaturan Pembayaran</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('backend.layouts.flash')

        <div class="row">
            {{-- Form Konfigurasi Midtrans --}}
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-credit-card mr-2"></i>Konfigurasi Midtrans
                        </h3>
                    </div>

                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="card-body">

                            {{-- Mode --}}
                            <div class="form-group">
                                <label>Mode</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="icheck-success">
                                            <input type="radio" id="mode_sandbox" name="is_production" value="0"
                                                {{ ($settings['midtrans_is_production']->value ?? '0') == '0' ? 'checked' : '' }}>
                                            <label for="mode_sandbox">
                                                <span class="badge badge-warning">Sandbox</span>
                                                &nbsp;Testing / Development
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="icheck-danger">
                                            <input type="radio" id="mode_production" name="is_production" value="1"
                                                {{ ($settings['midtrans_is_production']->value ?? '0') == '1' ? 'checked' : '' }}>
                                            <label for="mode_production">
                                                <span class="badge badge-success">Production</span>
                                                &nbsp;Live / Transaksi Nyata
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @error('is_production')
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr>

                            {{-- Merchant ID --}}
                            <div class="form-group">
                                <label for="merchant_id">Merchant ID</label>
                                <input type="text" id="merchant_id" name="merchant_id"
                                    class="form-control @error('merchant_id') is-invalid @enderror"
                                    value="{{ old('merchant_id', $settings['midtrans_merchant_id']->value ?? '') }}"
                                    placeholder="Gxxxxxxxxxx">
                                <small class="text-muted">Ditemukan di Midtrans Dashboard → Settings → Access Keys.</small>
                                @error('merchant_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Server Key --}}
                            <div class="form-group">
                                <label for="server_key">
                                    Server Key
                                    <span class="badge badge-secondary ml-1">Secret</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" id="server_key" name="server_key"
                                        class="form-control @error('server_key') is-invalid @enderror"
                                        placeholder="{{ filled($settings['midtrans_server_key']->value ?? null) ? '••••••••••••••••••••' : 'SB-Mid-server-xxxx...' }}"
                                        autocomplete="new-password">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary toggle-password" type="button"
                                            data-target="server_key">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah key yang tersimpan.</small>
                                @error('server_key')
                                    <div class="text-danger text-sm">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Client Key --}}
                            <div class="form-group">
                                <label for="client_key">
                                    Client Key
                                    <span class="badge badge-secondary ml-1">Secret</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" id="client_key" name="client_key"
                                        class="form-control @error('client_key') is-invalid @enderror"
                                        placeholder="{{ filled($settings['midtrans_client_key']->value ?? null) ? '••••••••••••••••••••' : 'SB-Mid-client-xxxx...' }}"
                                        autocomplete="new-password">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary toggle-password" type="button"
                                            data-target="client_key">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah key yang tersimpan.</small>
                                @error('client_key')
                                    <div class="text-danger text-sm">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr>

                            {{-- 3DS --}}
                            <div class="form-group">
                                <label>3D Secure (3DS)</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="icheck-primary">
                                            <input type="radio" id="3ds_on" name="is_3ds" value="1"
                                                {{ ($settings['midtrans_is_3ds']->value ?? '1') == '1' ? 'checked' : '' }}>
                                            <label for="3ds_on">Aktif <small class="text-muted">(Direkomendasikan)</small></label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="icheck-warning">
                                            <input type="radio" id="3ds_off" name="is_3ds" value="0"
                                                {{ ($settings['midtrans_is_3ds']->value ?? '1') == '0' ? 'checked' : '' }}>
                                            <label for="3ds_off">Nonaktif</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notification URL --}}
                            <div class="form-group">
                                <label for="notification_url">Notification URL (Webhook)</label>
                                <input type="url" id="notification_url" name="notification_url"
                                    class="form-control @error('notification_url') is-invalid @enderror"
                                    value="{{ old('notification_url', $settings['midtrans_notif_url']->value ?? config('app.url').'/payment/webhook/midtrans') }}">
                                <small class="text-muted">
                                    URL ini harus didaftarkan di
                                    <a href="https://dashboard.sandbox.midtrans.com/settings/payment" target="_blank">
                                        Midtrans Dashboard <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
                                    → Settings → Payment Notification URL.
                                    Saat development lokal, gunakan
                                    <a href="https://ngrok.com" target="_blank">ngrok <i class="fas fa-external-link-alt fa-xs"></i></a>.
                                </small>
                                @error('notification_url')
                                    <div class="text-danger text-sm">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <form action="{{ route('admin.settings.payment.test') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-info">
                                    <i class="fas fa-plug mr-1"></i> Test Koneksi
                                </button>
                            </form>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Panel Info --}}
            <div class="col-lg-4">

                {{-- Status Koneksi --}}
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Status</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">Mode</td>
                                <td>
                                    @if(($settings['midtrans_is_production']->value ?? '0') == '1')
                                        <span class="badge badge-success">Production</span>
                                    @else
                                        <span class="badge badge-warning">Sandbox</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Merchant ID</td>
                                <td><code>{{ $settings['midtrans_merchant_id']->value ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Server Key</td>
                                <td>
                                    @if(filled($settings['midtrans_server_key']->value ?? null))
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Tersimpan</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Belum diisi</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Client Key</td>
                                <td>
                                    @if(filled($settings['midtrans_client_key']->value ?? null))
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Tersimpan</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Belum diisi</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">3DS</td>
                                <td>
                                    @if(($settings['midtrans_is_3ds']->value ?? '1') == '1')
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Webhook</td>
                                <td>
                                    <code class="text-xs" style="word-break:break-all;">
                                        {{ $settings['midtrans_notif_url']->value ?? '-' }}
                                    </code>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Panduan --}}
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-book mr-2"></i>Cara Mendapatkan Key</h3>
                    </div>
                    <div class="card-body p-3">
                        <ol class="pl-3 mb-0" style="font-size:0.85rem; line-height:1.8">
                            <li>Login ke <a href="https://dashboard.sandbox.midtrans.com" target="_blank">Midtrans Dashboard</a></li>
                            <li>Pilih environment <strong>Sandbox</strong> (untuk testing)</li>
                            <li>Buka <strong>Settings → Access Keys</strong></li>
                            <li>Salin <strong>Server Key</strong> dan <strong>Client Key</strong></li>
                            <li>Paste di form ini lalu klik <strong>Simpan</strong></li>
                            <li>Daftarkan Notification URL di <strong>Settings → Payment</strong></li>
                        </ol>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Toggle show/hide password field
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
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
