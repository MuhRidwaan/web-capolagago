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

    $reviewAllowed = (bool) ($reviewEligibility['allowed'] ?? false);
    $reviewMessage = (string) ($reviewEligibility['message'] ?? '');
    $oldReviewProductId = (int) old('product_id', 0);
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

                @if (session('review_success'))
                    <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        {{ session('review_success') }}
                    </div>
                @endif

                @if (session('review_error'))
                    <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                        {{ session('review_error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        {{ $errors->first() }}
                    </div>
                @endif

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
                            <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $item->product_name_snapshot }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $item->is_addon ? 'Add-on' : 'Produk utama' }} • {{ $item->quantity }} x Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                                    </p>
                                </div>
                                <p class="text-sm font-bold text-slate-900 sm:text-right">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</p>
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

            <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
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
                                    <p class="mt-1 text-sm text-slate-600">
                                        Ceritakan pengalamanmu agar calon tamu lain lebih yakin sebelum booking.
                                    </p>
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

        <aside class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="text-lg font-bold text-slate-900">Status Pembayaran</h2>
                @if ($payment)
                    <div class="mt-4 space-y-3 text-sm text-slate-700">
                        <div class="flex items-start justify-between gap-3">
                            <span>Metode</span>
                            <span class="text-right font-semibold text-slate-900">{{ $payment->payment_method_name ?? '-' }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <span>Referensi</span>
                            <span class="break-all text-right font-semibold text-slate-900">{{ $payment->payment_code }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <span>Total</span>
                            <span class="text-right font-semibold text-slate-900">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</span>
                        </div>
                        @if ($payment->paid_at)
                            <div class="flex items-start justify-between gap-3">
                                <span>Dibayar pada</span>
                                <span class="text-right font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($payment->paid_at)->translatedFormat('d F Y H:i') }}</span>
                            </div>
                        @endif
                        @if ($payment->expired_at)
                            <div class="flex items-start justify-between gap-3">
                                <span>Batas bayar</span>
                                <span class="text-right font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($payment->expired_at)->translatedFormat('d F Y H:i') }}</span>
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
                                    data-resume-url="{{ route('ticket.booking.resume-payment', ['token' => $booking->public_token], false) }}"
                                    class="mt-3 inline-flex w-full items-center justify-center rounded-2xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                                    Lanjutkan Pembayaran
                                </button>
                                <button type="button"
                                    id="sync-payment-button"
                                    data-sync-url="{{ route('ticket.booking.sync-payment', ['token' => $booking->public_token], false) }}"
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
                    <div class="flex items-start justify-between gap-3">
                        <span>Subtotal</span>
                        <span class="text-right">Rp {{ number_format((float) $booking->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span>Service fee</span>
                        <span class="text-right">Rp {{ number_format((float) $booking->service_fee, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-slate-200 pt-3">
                        <div class="flex items-start justify-between gap-3 text-base font-bold text-slate-900">
                            <span>Total</span>
                            <span class="text-right">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</section>

@if ($payment && ($payment->status ?? null) === 'pending')
    <div id="payment-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 px-4 py-6">
        <div class="max-h-[calc(100vh-3rem)] w-full max-w-md overflow-y-auto rounded-[28px] bg-white shadow-2xl">
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
                        <p class="break-words text-2xl font-bold text-slate-900 sm:text-3xl">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
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
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
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
                        <div class="mt-2 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
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
                        <div class="mt-2 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
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
                    setFeedback(
                        result.message || 'Detail pembayaran tersedia di halaman ini. Silakan lanjutkan dengan referensi pembayaran yang sudah ada.',
                        'warning'
                    );

                    if (modal) {
                        openModal();
                    }

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
                resumeButton.textContent = 'Lanjutkan Pembayaran';
            }
        });

        // Auto-sync status saat halaman dimuat (tanpa buka modal)
        // Ini untuk handle kasus redirect dari Snap setelah bayar
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto_sync') === '1' && syncButton) {
            handleSyncStatus(syncButton);
        }
    })();
</script>
@endif
@endsection
