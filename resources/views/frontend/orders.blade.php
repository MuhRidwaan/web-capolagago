@extends('frontend.layouts.main')

@section('title', 'Pesanan Saya - CapolagaGo')
@section('meta_description', 'Lihat semua riwayat pesanan dan status booking customer CapolagaGo.')

@section('content')
@include('frontend.layouts.header')

@php
    $bookingStatusTone = static function (?string $status): string {
        return match ($status) {
            'confirmed', 'checked_in', 'completed', 'paid' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',
            'pending', 'waiting_payment' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',
            'cancelled', 'failed', 'expired', 'refunded' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200',
            default => 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200',
        };
    };
@endphp

<section class="pt-20 sm:pt-24 md:pt-28">
    <div class="border-b border-slate-200 bg-[linear-gradient(135deg,#153847_0%,#2a6e72_55%,#66a7a8_100%)]">
        <div class="mx-auto max-w-[1920px] px-4 py-12 md:px-8 md:py-16">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-white/70">Customer Area</p>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-white sm:text-4xl">Pesanan Saya</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
                    Pantau semua paket yang pernah Anda pesan, status pembayaran, dan detail kunjungan dari satu halaman.
                </p>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-[1920px] px-4 py-8 md:px-8 md:py-10">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Total Paket Dipesan</p>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $bookings->count() }}</p>
            </div>
            <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Menunggu Pembayaran</p>
                <p class="mt-3 text-3xl font-bold text-amber-600">{{ $bookings->whereIn('status', ['pending', 'waiting_payment'])->count() }}</p>
            </div>
            <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Selesai / Aktif</p>
                <p class="mt-3 text-3xl font-bold text-emerald-600">{{ $bookings->whereIn('status', ['confirmed', 'checked_in', 'completed'])->count() }}</p>
            </div>
        </div>

        <section class="mt-6 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-100 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Riwayat Pesanan</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Paket yang Dipesan</h2>
                </div>
                <a href="{{ route('frontend.profile') }}" class="text-sm font-medium text-teal-700 transition hover:text-teal-800">Kembali ke Profile</a>
            </div>

            @forelse ($bookings as $booking)
                <article class="border-b border-slate-100 py-5 last:border-b-0 last:pb-0">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $booking->main_package }}</h3>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $bookingStatusTone($booking->status) }}">
                                    {{ $statusLabels[$booking->status] ?? ucfirst((string) $booking->status) }}
                                </span>
                                @if ($booking->payment_status)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                        Bayar: {{ $statusLabels[$booking->payment_status] ?? ucfirst((string) $booking->payment_status) }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                                <p>Kode Booking: <span class="font-semibold text-slate-900">{{ $booking->booking_code }}</span></p>
                                <p>Tanggal Kunjungan: <span class="font-semibold text-slate-900">{{ \Carbon\Carbon::parse($booking->visit_date)->translatedFormat('d M Y') }}</span></p>
                                <p>Jumlah Peserta: <span class="font-semibold text-slate-900">{{ $booking->total_guests }} orang</span></p>
                                <p>Total Bayar: <span class="font-semibold text-slate-900">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</span></p>
                            </div>

                            @if ($booking->items->isNotEmpty())
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($booking->items as $item)
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700">
                                            {{ $item->product_name_snapshot }} x{{ $item->quantity }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="flex shrink-0 flex-col gap-3 sm:flex-row lg:flex-col">
                            <a href="{{ route('ticket.booking.status', ['token' => $booking->public_token]) }}" class="inline-flex items-center justify-center rounded-2xl bg-teal-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-teal-700">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="py-10 text-center">
                    <p class="text-lg font-semibold text-slate-900">Belum ada paket yang dipesan.</p>
                    <p class="mt-2 text-sm text-slate-500">Mulai booking pengalaman pertama Anda di Capolaga.</p>
                    <a href="{{ route('ticket.booking') }}" class="mt-5 inline-flex items-center justify-center rounded-2xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700">
                        Mulai Booking
                    </a>
                </div>
            @endforelse
        </section>
    </div>
</section>
@endsection
