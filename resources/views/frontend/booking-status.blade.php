@extends('frontend.layouts.main')

@section('content')
@include('frontend.layouts.header')

@if ($midtransClientKey !== '')
    <script type="text/javascript"
        src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ $midtransClientKey }}"></script>
@endif

@php
    $bookingStatusTone = match ($booking->status) {
        'confirmed', 'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'waiting_payment', 'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
        'cancelled', 'failed', 'expired' => 'bg-rose-100 text-rose-800 border-rose-200',
        'refunded' => 'bg-slate-200 text-slate-700 border-slate-300',
        default => 'bg-sky-100 text-sky-800 border-sky-200',
    };

    $paymentStatusTone = match ($payment->status ?? null) {
        'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
        'failed', 'expired' => 'bg-rose-100 text-rose-800 border-rose-200',
        'refunded' => 'bg-slate-200 text-slate-700 border-slate-300',
        default => 'bg-sky-100 text-sky-800 border-sky-200',
    };

    $paymentExpiryIso = $payment && $payment->expired_at
        ? \Illuminate\Support\Carbon::parse($payment->expired_at)->toIso8601String()
        : null;

    $paymentReferenceLabel = $payment && $payment->va_number
        ? 'Virtual account number'
        : 'Referensi pembayaran';

    $paymentReferenceValue = $payment && $payment->va_number
        ? $payment->va_number
        : ($payment->payment_code ?? null);
@endphp

<section class="border-b border-slate-200 bg-white pt-20 md:pt-24">
    <div class="mx-auto max-w-[1200px] px-4 py-6 sm:px-6 lg:px-8">
        <a href="{{ route('ticket.booking') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 transition hover:text-slate-900">
            <span aria-hidden="true">←</span>
            <span>Kembali ke booking</span>
        </a>

        <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Booking Status</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Booking {{ $booking->booking_code }}</h1>
                <p class="mt-2 text-sm text-slate-600">
                    <!-- Status halaman ini dibaca langsung dari database booking dan payment terbaru. -->
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full border px-4 py-2 text-sm font-semibold {{ $bookingStatusTone }}">
                    Booking: {{ $statusLabels[$booking->status] ?? ucfirst($booking->status) }}
                </span>
                @if ($payment)
                    <span class="rounded-full border px-4 py-2 text-sm font-semibold {{ $paymentStatusTone }}">
                        Payment: {{ $statusLabels[$payment->status] ?? ucfirst($payment->status) }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="bg-slate-50 py-8">
    <div class="mx-auto grid max-w-[1200px] gap-6 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:px-8">
        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                <h2 class="text-lg font-bold text-slate-900">Ringkasan Booking</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Atas Nama</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $booking->customer_name }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $booking->customer_email }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Tanggal Kunjungan</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($booking->visit_date)->translatedFormat('d F Y') }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $booking->total_guests }} tamu</p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-slate-200">
                    <div class="border-b border-slate-200 px-4 py-3">
                        <h3 class="text-sm font-semibold text-slate-900">Item Booking</h3>
                    </div>
                    <div class="divide-y divide-slate-200">
                        @foreach ($items as $item)
                            <div class="flex items-start justify-between gap-4 px-4 py-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $item->product_name_snapshot }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $item->is_addon ? 'Add-on' : 'Produk utama' }} • {{ $item->quantity }} x Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                                    </p>
                                </div>
                                <p class="text-sm font-bold text-slate-900">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($booking->notes)
                    <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Catatan</p>
                        <p class="mt-2 text-sm text-slate-700">{{ $booking->notes }}</p>
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="text-lg font-bold text-slate-900">Status Pembayaran</h2>
                @if ($payment)
                    <div class="mt-4 space-y-3 text-sm text-slate-700">
                        <div class="flex items-center justify-between">
                            <span>Metode</span>
                            <span class="font-semibold text-slate-900">{{ $payment->payment_method_name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Referensi</span>
                            <span class="font-semibold text-slate-900">{{ $payment->payment_code }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Total</span>
                            <span class="font-semibold text-slate-900">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</span>
                        </div>
                        @if ($payment->paid_at)
                            <div class="flex items-center justify-between">
                                <span>Dibayar pada</span>
                                <span class="font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($payment->paid_at)->translatedFormat('d F Y H:i') }}</span>
                            </div>
                        @endif
                        @if ($payment->expired_at)
                            <div class="flex items-center justify-between">
                                <span>Batas bayar</span>
                                <span class="font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($payment->expired_at)->translatedFormat('d F Y H:i') }}</span>
                            </div>
                        @endif
                        @if ($payment->va_number)
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Virtual Account</p>
                                <p class="mt-2 text-base font-bold text-slate-900">{{ $payment->va_number }}</p>
                            </div>
                        @endif
                        @if (($payment->payment_provider ?? null) === 'midtrans' && ($payment->status ?? null) === 'pending')
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-sm text-emerald-900">Pembayaran masih pending. Kamu bisa lanjutkan pembayaran Midtrans dari halaman ini.</p>
                                <button type="button"
                                    id="open-payment-detail-button"
                                    class="mt-3 inline-flex w-full items-center justify-center rounded-2xl border border-emerald-300 bg-white px-4 py-2.5 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50">
                                    Lihat Detail Pembayaran
                                </button>
                                <button type="button"
                                    id="resume-payment-button"
                                    data-resume-url="{{ route('ticket.booking.resume-payment', ['token' => $booking->public_token]) }}"
                                    class="mt-3 inline-flex w-full items-center justify-center rounded-2xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                                    Lanjutkan Pembayaran
                                </button>
                                <button type="button"
                                    id="sync-payment-button"
                                    data-sync-url="{{ route('ticket.booking.sync-payment', ['token' => $booking->public_token]) }}"
                                    class="mt-2 inline-flex w-full items-center justify-center rounded-2xl border border-emerald-300 bg-white px-4 py-2.5 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50">
                                    Cek Status Pembayaran
                                </button>
                                <p id="resume-payment-feedback" class="mt-3 hidden rounded-xl bg-white px-3 py-2 text-xs text-slate-600"></p>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="mt-4 text-sm text-slate-600">Belum ada data pembayaran untuk booking ini.</p>
                @endif
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="text-lg font-bold text-slate-900">Total Tagihan</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format((float) $booking->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Service fee</span>
                        <span>Rp {{ number_format((float) $booking->service_fee, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-slate-200 pt-3">
                        <div class="flex items-center justify-between text-base font-bold text-slate-900">
                            <span>Total</span>
                            <span>Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</section>

@if ($payment && ($payment->status ?? null) === 'pending')
    <div id="payment-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 px-4 py-6">
        <div class="w-full max-w-md overflow-hidden rounded-[28px] bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 bg-slate-800 px-5 py-4 text-white">
                <div>
                    <p class="text-sm font-semibold">{{ config('app.name') }}</p>
                    <p class="mt-1 text-xs text-slate-300">Order ID #{{ $payment->payment_code }}</p>
                </div>
                <button type="button" id="close-payment-detail-button" class="text-xl leading-none text-white/80 transition hover:text-white">&times;</button>
            </div>

            <div class="border-b border-slate-200 px-5 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-3xl font-bold text-slate-900">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                        <p class="mt-2 text-xs text-slate-500">Booking {{ $booking->booking_code }}</p>
                    </div>
                    <button type="button" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        Details
                    </button>
                </div>
            </div>

            <div class="border-b border-slate-200 bg-slate-50 px-5 py-3 text-center">
                <p class="text-sm font-medium text-slate-700">
                    Bayar sebelum
                    <span id="payment-expiry-countdown" class="font-bold text-slate-900">--:--:--</span>
                </p>
            </div>

            <div class="space-y-5 px-5 py-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-lg font-bold text-slate-900">{{ $payment->payment_method_name ?? 'Metode Pembayaran' }}</p>
                        <p class="mt-1 text-sm text-slate-600">
                            @if ($payment->va_number)
                                Selesaikan pembayaran menggunakan virtual account di bawah ini.
                            @elseif ($payment->qr_url)
                                Scan QR untuk menyelesaikan pembayaran.
                            @else
                                Gunakan referensi pembayaran berikut untuk menyelesaikan transaksi.
                            @endif
                        </p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        {{ strtoupper((string) ($payment->payment_provider ?? $payment->payment_method_name ?? 'PAY')) }}
                    </span>
                </div>

                @if ($payment->va_number)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Virtual account number</p>
                        <div class="mt-2 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <p class="break-all text-lg font-bold text-slate-900">{{ $payment->va_number }}</p>
                            <button type="button" data-copy-value="{{ $payment->va_number }}" class="payment-copy-button text-sm font-semibold text-indigo-600 transition hover:text-indigo-800">
                                Copy
                            </button>
                        </div>
                    </div>
                @elseif ($payment->qr_url)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">QR Pembayaran</p>
                        <a href="{{ $payment->qr_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                            Buka QR pembayaran
                        </a>
                    </div>
                @else
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $paymentReferenceLabel }}</p>
                        <div class="mt-2 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <p class="break-all text-base font-bold text-slate-900">{{ $paymentReferenceValue }}</p>
                            <button type="button" data-copy-value="{{ $paymentReferenceValue }}" class="payment-copy-button text-sm font-semibold text-indigo-600 transition hover:text-indigo-800">
                                Copy
                            </button>
                        </div>
                    </div>
                @endif

                <details class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                    <summary class="cursor-pointer text-sm font-semibold text-indigo-600">How to pay</summary>
                    <div class="mt-3 space-y-2 text-sm text-slate-600">
                        <p>1. Salin nomor pembayaran atau buka QR sesuai metode yang dipilih.</p>
                        <p>2. Selesaikan pembayaran sebelum batas waktu berakhir.</p>
                        <p>3. Klik tombol cek status untuk memperbarui status pembayaran.</p>
                    </div>
                </details>
            </div>

            <div class="px-5 pb-5">
                <button type="button" id="modal-sync-payment-button" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-800 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-900">
                    Check status
                </button>
            </div>
        </div>
    </div>
@endif

@if ($payment && ($payment->payment_provider ?? null) === 'midtrans' && ($payment->status ?? null) === 'pending')
<script>
    (() => {
        const resumeButton = document.getElementById('resume-payment-button');
        const syncButton = document.getElementById('sync-payment-button');
        const feedbackBox = document.getElementById('resume-payment-feedback');
        const modal = document.getElementById('payment-detail-modal');
        const openModalButton = document.getElementById('open-payment-detail-button');
        const closeModalButton = document.getElementById('close-payment-detail-button');
        const modalSyncButton = document.getElementById('modal-sync-payment-button');
        const countdownBox = document.getElementById('payment-expiry-countdown');

        if (!resumeButton) {
            return;
        }

        const setFeedback = (message, tone = 'info') => {
            const tones = {
                info: 'bg-white text-slate-600',
                warning: 'border border-amber-200 bg-amber-50 text-amber-900',
                danger: 'border border-rose-200 bg-rose-50 text-rose-900',
                success: 'border border-emerald-200 bg-emerald-50 text-emerald-900',
            };

            feedbackBox.className = `mt-3 rounded-xl px-3 py-2 text-xs ${tones[tone] || tones.info}`;
            feedbackBox.textContent = message;
            feedbackBox.classList.remove('hidden');
        };

        const openModal = () => {
            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        const closeModal = () => {
            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        document.querySelectorAll('.payment-copy-button').forEach((button) => {
            button.addEventListener('click', async () => {
                const value = button.dataset.copyValue || '';

                if (!value) {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(value);
                    button.textContent = 'Copied';
                    window.setTimeout(() => {
                        button.textContent = 'Copy';
                    }, 1200);
                } catch (error) {
                    setFeedback('Gagal menyalin nomor pembayaran.', 'danger');
                }
            });
        });

        if (openModalButton) {
            openModalButton.addEventListener('click', openModal);
        }

        if (closeModalButton) {
            closeModalButton.addEventListener('click', closeModal);
        }

        if (modal) {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });
        }

        const expiredAtIso = @json($paymentExpiryIso);
        const tickCountdown = () => {
            if (!countdownBox || !expiredAtIso) {
                return;
            }

            const diff = new Date(expiredAtIso).getTime() - Date.now();

            if (diff <= 0) {
                countdownBox.textContent = '00:00:00';
                return;
            }

            const totalSeconds = Math.floor(diff / 1000);
            const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
            const seconds = String(totalSeconds % 60).padStart(2, '0');

            countdownBox.textContent = `${hours}:${minutes}:${seconds}`;
        };

        tickCountdown();
        window.setInterval(tickCountdown, 1000);

        // Tombol cek status
        const handleSyncStatus = async (button) => {
            button.disabled = true;
            button.textContent = 'Mengecek...';

            try {
                const response = await fetch(syncButton.dataset.syncUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json();

                if (result.status === 'paid') {
                    setFeedback('Pembayaran terkonfirmasi! Halaman akan direfresh...', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else if (result.status === 'pending') {
                    setFeedback('Pembayaran masih pending di Midtrans.', 'warning');
                } else {
                    setFeedback(result.message || 'Status: ' + result.status, 'warning');
                }
            } catch (error) {
                setFeedback('Gagal mengecek status: ' + error.message, 'danger');
            } finally {
                button.disabled = false;
                button.textContent = button === modalSyncButton ? 'Check status' : 'Cek Status Pembayaran';
            }
        };

        if (syncButton) {
            syncButton.addEventListener('click', () => handleSyncStatus(syncButton));
        }

        if (modalSyncButton) {
            modalSyncButton.addEventListener('click', () => handleSyncStatus(modalSyncButton));
        }

        resumeButton.addEventListener('click', async () => {
            resumeButton.disabled = true;
            resumeButton.textContent = 'Menyiapkan pembayaran...';

            try {
                const response = await fetch(resumeButton.dataset.resumeUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Snap token Midtrans belum bisa disiapkan.');
                }

                if (!result.payment_gateway?.snap_token || !window.snap) {
                    throw new Error('Snap Midtrans belum tersedia di browser ini.');
                }

                setFeedback('Snap token berhasil disiapkan. Popup pembayaran akan dibuka.', 'info');

                window.snap.pay(result.payment_gateway.snap_token, {
                    onSuccess: () => {
                        window.location.href = result.redirect_url;
                    },
                    onPending: () => {
                        window.location.href = result.redirect_url;
                    },
                    onClose: () => {
                        setFeedback('Popup pembayaran ditutup. Booking masih menunggu pembayaran.', 'warning');
                    },
                    onError: () => {
                        setFeedback('Terjadi kendala saat membuka pembayaran Midtrans.', 'danger');
                    },
                });
            } catch (error) {
                setFeedback(error.message || 'Gagal melanjutkan pembayaran.', 'danger');
            } finally {
                resumeButton.disabled = false;
                resumeButton.textContent = 'Lanjutkan Pembayaran';
            }
        });

        openModal();
    })();
</script>
@endif
@endsection
