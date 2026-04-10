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
        'confirmed', 'completed' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
        'waiting_payment', 'pending' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
        'cancelled', 'failed', 'expired' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
        'refunded' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
        default => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
    };

    $paymentStatusTone = match ($payment->status ?? null) {
        'paid' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
        'pending' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
        'failed', 'expired' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
        'refunded' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
        default => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
    };

    $paymentExpiryIso = $payment && $payment->expired_at
        ? \Illuminate\Support\Carbon::parse($payment->expired_at)->toIso8601String()
        : null;

    $reviewAllowed = (bool) ($reviewEligibility['allowed'] ?? false);
    $reviewMessage = (string) ($reviewEligibility['message'] ?? '');
    $oldReviewProductId = (int) old('product_id', 0);
    $mainPackage = $items->firstWhere('is_addon', false);
    $addons = $items->where('is_addon', true)->values();
    $paymentStatusLabel = $statusLabels[$payment->status ?? 'pending'] ?? ucfirst((string) ($payment->status ?? 'pending'));
@endphp

<section class="bg-white pt-20 md:pt-24">
    <div class="mx-auto max-w-[920px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
            <a href="{{ route('frontend.orders') }}" class="transition hover:text-slate-900">Pesanan Saya</a>
            <span>/</span>
            <span class="text-slate-900">{{ $booking->booking_code }}</span>
        </div>

        <div class="mx-auto max-w-[560px]">
            <div class="mb-5 flex items-start gap-3">
                <a href="{{ route('frontend.orders') }}" class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-700 transition hover:bg-slate-50">
                    <span aria-hidden="true">&larr;</span>
                </a>
                <div>
                    <h1 class="text-[2rem] font-bold tracking-tight text-slate-900">Lanjutkan Pembayaran</h1>
                    <p class="mt-1 text-sm text-slate-500">Booking {{ $booking->booking_code }}</p>
                </div>
            </div>

            @if (session('review_success'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    {{ session('review_success') }}
                </div>
            @endif

            @if (session('review_error'))
                <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                    {{ session('review_error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm">
                <div class="divide-y divide-slate-200 text-sm">
                    <div class="flex items-center justify-between gap-4 px-4 py-4">
                        <span class="text-slate-500">Kode Booking</span>
                        <span class="font-semibold text-slate-900">{{ $booking->booking_code }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 px-4 py-4">
                        <span class="text-slate-500">Nama Paket</span>
                        <span class="text-right font-semibold text-slate-900">{{ $mainPackage->product_name_snapshot ?? 'Paket Wisata' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 px-4 py-4">
                        <span class="text-slate-500">Tanggal Kunjungan</span>
                        <span class="font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($booking->visit_date)->translatedFormat('d M Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 px-4 py-4">
                        <span class="text-slate-500">Total Bayar</span>
                        <span class="text-right text-[1.65rem] font-bold text-emerald-700">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </section>

            <div class="mt-5">
                <div class="mb-3 flex flex-wrap gap-2">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $bookingStatusTone }}">
                        Booking: {{ $statusLabels[$booking->status] ?? ucfirst((string) $booking->status) }}
                    </span>
                    @if ($payment)
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $paymentStatusTone }}">
                            Pembayaran: {{ $paymentStatusLabel }}
                        </span>
                    @endif
                </div>

                <p class="mb-3 text-sm font-semibold text-slate-900">Pilih Metode Pembayaran:</p>

                @if ($payment)
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-4 rounded-[18px] border border-slate-200 bg-white px-4 py-4">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                                    @if (($payment->payment_type ?? '') === 'bank_transfer' || $payment->va_number)
                                        <span aria-hidden="true">🏦</span>
                                    @elseif ($payment->qr_url)
                                        <span aria-hidden="true">▦</span>
                                    @else
                                        <span aria-hidden="true">💳</span>
                                    @endif
                                </span>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $payment->payment_method_name ?? 'Metode Pembayaran' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $payment->va_number ?: ($payment->payment_code ?? 'Referensi pembayaran tersedia di invoice') }}</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-emerald-700">
                                {{ strtoupper((string) ($payment->payment_type ?? 'pay')) }}
                            </span>
                        </div>

                        @if ($payment->qr_url)
                            <a href="{{ $payment->qr_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between gap-4 rounded-[18px] border border-slate-200 bg-white px-4 py-4 text-sm transition hover:bg-slate-50">
                                <div>
                                    <p class="font-semibold text-slate-900">QR Pembayaran</p>
                                    <p class="mt-1 text-xs text-slate-500">Buka atau scan QR untuk menyelesaikan transaksi</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-slate-600">QRIS</span>
                            </a>
                        @endif
                    </div>
                @else
                    <div class="rounded-[18px] border border-slate-200 bg-white px-4 py-4 text-sm text-slate-600">
                        Belum ada data pembayaran untuk booking ini.
                    </div>
                @endif
            </div>

            <div class="mt-5 space-y-3">
                @if ($payment && ($payment->payment_provider ?? null) === 'midtrans' && ($payment->status ?? null) === 'pending')
                    <button type="button"
                        id="resume-payment-button"
                        data-resume-url="{{ route('ticket.booking.resume-payment', ['token' => $booking->public_token], false) }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-emerald-700 px-5 py-4 text-sm font-semibold text-white transition hover:bg-emerald-800">
                        <span aria-hidden="true">◌</span>
                        Pilih &amp; Lihat Instruksi
                    </button>
                @endif

                <a href="{{ route('ticket.booking.invoice', ['token' => $booking->public_token]) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    <span aria-hidden="true">🧾</span>
                    Lihat Invoice &amp; Status
                </a>

                @if ($payment && ($payment->payment_provider ?? null) === 'midtrans' && ($payment->status ?? null) === 'pending')
                    <button type="button"
                        id="sync-payment-button"
                        data-sync-url="{{ route('ticket.booking.sync-payment', ['token' => $booking->public_token], false) }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        <span aria-hidden="true">↻</span>
                        Cek Status Pembayaran
                    </button>
                @endif

                <a href="{{ route('frontend.home') }}" class="inline-flex w-full items-center justify-center gap-2 px-5 py-2 text-sm text-slate-600 transition hover:text-slate-900">
                    <span aria-hidden="true">⌂</span>
                    Kembali ke Beranda
                </a>

                @if ($payment && ($payment->status ?? null) === 'pending')
                    <p id="resume-payment-feedback" class="hidden rounded-xl bg-slate-50 px-3 py-2 text-xs text-slate-600"></p>
                @endif
            </div>
        </div>

        <div class="mx-auto mt-10 max-w-[920px] grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Ringkasan Booking</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-900">Detail Pesanan</h2>
                    </div>
                    @if ($payment?->expired_at)
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Batas Bayar</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($payment->expired_at)->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                    @endif
                </div>

                <div class="mt-5 rounded-[20px] border border-slate-200">
                    <div class="divide-y divide-slate-200">
                        @foreach ($items as $item)
                            <div class="flex items-start justify-between gap-4 px-4 py-4">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900">{{ $item->product_name_snapshot }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $item->is_addon ? 'Add-on' : 'Produk utama' }} • {{ $item->quantity }} x Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-bold text-slate-900">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($booking->notes)
                    <div class="mt-5 rounded-[18px] bg-amber-50 px-4 py-4 text-sm text-amber-900">
                        <p class="font-semibold">Catatan Booking</p>
                        <p class="mt-1">{{ $booking->notes }}</p>
                    </div>
                @endif
            </section>

            <aside class="space-y-5">
                <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Customer</p>
                    <div class="mt-3 space-y-2 text-sm text-slate-600">
                        <p class="font-semibold text-slate-900">{{ $booking->customer_name }}</p>
                        <p>{{ $booking->customer_email }}</p>
                        <p>{{ $booking->total_guests }} orang • {{ \Illuminate\Support\Carbon::parse($booking->visit_date)->translatedFormat('d M Y') }}</p>
                    </div>
                </section>

                <section class="rounded-[24px] border border-slate-200 bg-[#1f2937] p-5 text-white shadow-sm">
                    <p class="text-sm text-white/70">Estimasi subtotal</p>
                    <div class="mt-3 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-white/70">Subtotal</span>
                            <span>Rp {{ number_format((float) $booking->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-white/70">Service fee</span>
                            <span>Rp {{ number_format((float) $booking->service_fee, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-white/10 pt-4">
                            <div class="flex items-center justify-between gap-4 text-lg font-bold">
                                <span>Total</span>
                                <span>Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <div class="mx-auto mt-10 max-w-[920px]">
            <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Tulis Ulasan</h2>
                        <p class="mt-1 text-sm text-slate-600">{{ $reviewMessage }}</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full border px-3 py-1 text-xs font-semibold {{ $reviewAllowed ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
                        {{ $reviewAllowed ? 'Ulasan dibuka' : 'Belum tersedia' }}
                    </span>
                </div>

                <div class="mt-5 space-y-4">
                    @foreach ($items->where('is_addon', false) as $item)
                        @php
                            $isActiveOldReview = $oldReviewProductId === (int) $item->product_id;
                            $currentRating = (int) ($isActiveOldReview ? old('rating', $item->review_rating) : ($item->review_rating ?? 0));
                            $currentComment = (string) ($isActiveOldReview ? old('comment', $item->review_comment) : ($item->review_comment ?? ''));
                        @endphp

                        <article class="rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-semibold text-slate-900">{{ $item->product_name_snapshot }}</h3>
                                        @if ($item->review_id)
                                            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                                Sudah diulas
                                            </span>
                                        @endif
                                    </div>
                                    @if ($item->product_slug)
                                        <a href="{{ route('frontend.wisata.show', ['slug' => $item->product_slug]) }}" class="mt-2 inline-flex text-sm font-semibold text-teal-700 transition hover:text-teal-800">
                                            Lihat halaman paket
                                        </a>
                                    @endif
                                </div>

                                @if ($item->review_id && $item->review_updated_at)
                                    <p class="text-xs text-slate-500">
                                        Diperbarui {{ \Illuminate\Support\Carbon::parse($item->review_updated_at)->translatedFormat('d M Y H:i') }}
                                    </p>
                                @endif
                            </div>

                            @if ($reviewAllowed)
                                <form action="{{ route('ticket.booking.review.store', ['token' => $booking->public_token]) }}" method="POST" class="mt-4 space-y-4">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item->product_id }}">

                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Rating</p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @for ($rating = 5; $rating >= 1; $rating--)
                                                <label class="cursor-pointer">
                                                    <input
                                                        type="radio"
                                                        name="rating"
                                                        value="{{ $rating }}"
                                                        class="peer sr-only"
                                                        {{ $currentRating === $rating ? 'checked' : '' }}
                                                    >
                                                    <span class="inline-flex rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 transition peer-checked:border-teal-600 peer-checked:bg-teal-600 peer-checked:text-white">
                                                        {{ $rating }} ★
                                                    </span>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>

                                    <div>
                                        <label for="review-comment-{{ $item->product_id }}" class="text-sm font-semibold text-slate-900">Ceritakan pengalamanmu</label>
                                        <textarea
                                            id="review-comment-{{ $item->product_id }}"
                                            name="comment"
                                            rows="4"
                                            maxlength="1000"
                                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                                            placeholder="Apa yang paling kamu suka, dan apa yang perlu ditingkatkan?"
                                        >{{ $currentComment }}</textarea>
                                    </div>

                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-xs text-slate-500">
                                            Ulasan akan langsung tampil di halaman paket selama masih relevan dan sopan.
                                        </p>
                                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-teal-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">
                                            {{ $item->review_id ? 'Perbarui Ulasan' : 'Kirim Ulasan' }}
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-600">
                                    Form ulasan akan dibuka otomatis setelah booking memenuhi syarat untuk diulas.
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</section>

@if ($payment && ($payment->payment_provider ?? null) === 'midtrans' && ($payment->status ?? null) === 'pending')
<script>
    (() => {
        const resumeButton = document.getElementById('resume-payment-button');
        const syncButton = document.getElementById('sync-payment-button');
        const feedbackBox = document.getElementById('resume-payment-feedback');
        if (!resumeButton || !syncButton) return;

        const setFeedback = (message, tone = 'info') => {
            const tones = {
                info: 'bg-slate-50 text-slate-600',
                warning: 'border border-amber-200 bg-amber-50 text-amber-900',
                danger: 'border border-rose-200 bg-rose-50 text-rose-900',
                success: 'border border-emerald-200 bg-emerald-50 text-emerald-900',
            };

            feedbackBox.className = `rounded-xl px-3 py-2 text-xs ${tones[tone] || tones.info}`;
            feedbackBox.textContent = message;
            feedbackBox.classList.remove('hidden');
        };

        const handleSyncStatus = async (button) => {
            button.disabled = true;
            const originalText = button.textContent;
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
                button.textContent = originalText;
            }
        };

        syncButton.addEventListener('click', () => handleSyncStatus(syncButton));

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
                    setFeedback(result.message || 'Detail pembayaran tersedia di invoice. Silakan lanjut sesuai instruksi.', 'warning');
                    return;
                }

                setFeedback('Snap token berhasil disiapkan. Popup pembayaran akan dibuka.', 'info');

                window.snap.pay(result.payment_gateway.snap_token, {
                    onSuccess: () => {
                        window.location.href = result.redirect_url + '?auto_sync=1';
                    },
                    onPending: () => {
                        window.location.href = result.redirect_url + '?auto_sync=1';
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
                resumeButton.textContent = 'Pilih & Lihat Instruksi';
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto_sync') === '1') {
            handleSyncStatus(syncButton);
        }
    })();
</script>
@endif
@endsection
