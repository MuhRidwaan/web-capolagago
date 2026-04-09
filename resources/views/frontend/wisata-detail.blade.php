@extends('frontend.layouts.main')

@php
    $pageTitle = ($product->meta_title ?: $product->name) . ' | Detail Paket Wisata';
    $pageDescription = $product->meta_desc ?: ($product->short_desc ?: 'Detail paket wisata, ulasan tamu, dan informasi biaya parkir untuk ' . $product->name . '.');
    $ratingValue = (float) (($reviewSummary->average_rating ?? 0) ?: $product->rating_avg);
    $reviewCount = (int) (($reviewSummary->total_reviews ?? 0) ?: $product->review_count);
    $tagGroups = $product->activityTags->groupBy('group_name');
    $prefilledDate = $prefilledDate ?? now()->toDateString();
    $prefilledGuests = $prefilledGuests ?? 2;

    $baseFallbackImage = static function () use ($product) {
        $slug = strtolower((string) $product->slug);
        $category = strtolower((string) ($product->category?->label ?? ''));

        return match (true) {
            str_contains($slug, 'glamping') || str_contains($category, 'glamping') => asset('images/glamping.jpg'),
            str_contains($slug, 'camping') || str_contains($category, 'camping') => asset('images/camping.jpg'),
            str_contains($slug, 'homestay') || str_contains($category, 'homestay') => asset('images/homestay.jpg'),
            str_contains($slug, 'rafting') || str_contains($category, 'rafting') => asset('images/rafting.jpg'),
            str_contains($slug, 'paralayang') || str_contains($slug, 'paragliding') => asset('images/paragliding.jpg'),
            str_contains($slug, 'atv') => asset('images/atv.jpg'),
            str_contains($slug, 'sari-ater') || str_contains($category, 'pemandian') => asset('images/hotspring.jpg'),
            default => asset('images/glamping.jpg'),
        };
    };

    $resolveImage = static function (?string $path, string $fallback) {
        if (blank($path)) {
            return $fallback;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $relativePath = 'uploads/' . ltrim($path, '/');

        return file_exists(public_path($relativePath))
            ? asset($relativePath)
            : $fallback;
    };

    $heroImage = $resolveImage($product->primaryImage?->image_path, $baseFallbackImage());
    $galleryImages = $product->images
        ->unique('image_path')
        ->filter(fn ($image) => filled($image->image_path))
        ->map(fn ($image) => [
            'src' => str_starts_with($image->image_path, 'http://') || str_starts_with($image->image_path, 'https://')
                ? $image->image_path
                : (file_exists(public_path('uploads/' . ltrim($image->image_path, '/')))
                    ? asset('uploads/' . ltrim($image->image_path, '/'))
                    : null),
            'alt' => $image->alt_text ?: $product->name,
        ])
        ->filter(fn ($image) => filled($image['src']))
        ->values();

    $bookingUrl = route('ticket.booking.product', [
        'slug' => $product->slug,
        'date' => $prefilledDate,
        'guests' => $prefilledGuests,
    ]);
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)

@section('content')
@include('frontend.layouts.header')

<section class="relative overflow-hidden pt-20 sm:pt-24">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(20,184,166,0.28),_transparent_34%),linear-gradient(135deg,#0b1220_0%,#0f766e_52%,#14b8a6_100%)]"></div>
    <div class="absolute left-[-6rem] top-[-5rem] h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
    <div class="absolute bottom-[-8rem] right-[-6rem] h-[26rem] w-[26rem] rounded-full bg-emerald-300/15 blur-3xl"></div>

    <div class="relative mx-auto max-w-[1680px] px-4 pb-12 pt-8 sm:px-6 lg:px-8 lg:pb-16">
        <div class="flex flex-wrap items-center gap-2 text-sm text-white/75">
            <a href="{{ route('frontend.home') }}" class="transition hover:text-white">Home</a>
            <span class="text-white/40">/</span>
            <a href="{{ route('frontend.wisata') }}" class="transition hover:text-white">Paket Wisata</a>
            <span class="text-white/40">/</span>
            <span class="text-white">{{ $product->name }}</span>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(300px,0.72fr)] xl:items-start">
            <article class="overflow-hidden rounded-[32px] bg-white shadow-[0_24px_70px_rgba(2,6,23,0.28)]">
                <div class="grid gap-0 lg:min-h-[560px] lg:grid-cols-[minmax(0,1.1fr)_minmax(340px,0.9fr)]">
                    <div class="relative min-h-[360px] overflow-hidden">
                        <img src="{{ $heroImage }}" alt="{{ $product->name }}" class="absolute inset-0 h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/35 to-slate-950/5"></div>

                        <div class="relative flex h-full flex-col justify-between p-6 sm:p-8 lg:p-10">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full bg-white/90 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-slate-800 shadow-sm">
                                    {{ $product->category?->label ?? 'Paket Wisata' }}
                                </span>
                                @if ($product->is_featured)
                                    <span class="rounded-full bg-amber-400 px-3 py-1.5 text-xs font-semibold text-amber-950 shadow-sm">Featured</span>
                                @endif
                            </div>

                            <div class="max-w-2xl">
                                <div class="flex flex-wrap gap-2 text-white/90">
                                    <span class="rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium backdrop-blur-sm">{{ $reviewCount }} ulasan</span>
                                    <span class="rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium backdrop-blur-sm">{{ $product->min_pax }}-{{ $product->max_pax }} tamu</span>
                                    <span class="rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium backdrop-blur-sm">{{ $product->duration_hours ? number_format((float) $product->duration_hours, 1) . ' jam' : 'Sesuai paket' }}</span>
                                </div>
                                <h1 class="mt-4 text-3xl font-bold leading-tight text-white sm:text-4xl lg:text-5xl">
                                    {{ $product->name }}
                                </h1>
                                <p class="mt-3 max-w-xl text-sm leading-7 text-white/85 sm:text-base">
                                    {{ $product->short_desc ?: 'Ringkasan paket wisata belum tersedia.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col bg-white p-6 sm:p-7 lg:p-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">Harga Paket</p>
                                <div class="mt-2 flex flex-wrap items-end gap-2">
                                    <p class="break-words text-3xl font-bold tracking-tight text-teal-700 sm:text-4xl">
                                        Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                                    </p>
                                    <span class="pb-1 text-sm font-medium text-slate-500">{{ $product->price_label }}</span>
                                </div>
                            </div>
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-lg font-bold text-teal-700">
                                Rp
                            </div>
                        </div>

                        <div class="mt-6 grid gap-3 rounded-[28px] border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-4 text-sm">
                                <span class="text-slate-500">Durasi</span>
                                <strong class="text-right text-slate-900">{{ $product->duration_hours ? number_format((float) $product->duration_hours, 1) . ' jam' : 'Menginap / sesuai paket' }}</strong>
                            </div>
                            <div class="flex items-start justify-between gap-4 text-sm">
                                <span class="text-slate-500">Kapasitas</span>
                                <strong class="text-right text-slate-900">{{ $product->max_capacity }} slot / unit</strong>
                            </div>
                            <div class="flex items-start justify-between gap-4 text-sm">
                                <span class="text-slate-500">Penyedia</span>
                                <strong class="text-right text-slate-900">{{ $product->mitra?->business_name ?? 'Capolaga' }}</strong>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ $bookingUrl }}" class="inline-flex items-center justify-center rounded-2xl bg-teal-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">
                                Booking Paket Ini
                            </a>
                            <a href="{{ route('frontend.wisata') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-teal-300 hover:text-teal-700">
                                Lihat Paket Lain
                            </a>
                        </div>

                        <div class="mt-6 rounded-[28px] bg-teal-50 px-4 py-4 text-sm leading-6 text-teal-900">
                            Harga, fasilitas, dan biaya parkir dapat berubah tergantung musim, hari libur, atau kebijakan pengelola.
                        </div>

                        <div class="mt-6 grid gap-3 sm:grid-cols-3">
                            @foreach ([
                                ['label' => 'Rating', 'value' => number_format($ratingValue, 1)],
                                ['label' => 'Ulasan', 'value' => $reviewCount . ' publik'],
                                ['label' => 'Tipe', 'value' => $product->category?->label ?? 'Paket Wisata'],
                            ] as $metric)
                                <div class="rounded-[20px] border border-slate-200 bg-white px-4 py-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">{{ $metric['label'] }}</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $metric['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </article>

            <aside class="self-start rounded-[28px] bg-white p-4 shadow-[0_24px_70px_rgba(2,6,23,0.18)] sm:p-5">
                <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-slate-500">Ringkasan Cepat</p>

                <div class="mt-4 grid gap-2.5">
                    <div class="flex min-h-[92px] flex-col rounded-3xl bg-slate-50 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Kategori</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $product->category?->label ?? 'Paket Wisata' }}</p>
                    </div>

                    <div class="flex min-h-[108px] flex-col rounded-3xl bg-slate-50 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Cocok Untuk</p>
                        <div class="mt-2.5 flex flex-1 flex-wrap content-start gap-2">
                            @forelse ($tagGroups->get('audience', collect()) as $tag)
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">{{ $tag->name }}</span>
                            @empty
                                <span class="text-sm text-slate-500">Belum ada tag audiens.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex min-h-[108px] flex-col rounded-3xl bg-slate-50 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Tema</p>
                        <div class="mt-2.5 flex flex-1 flex-wrap content-start gap-2">
                            @forelse ($tagGroups->get('theme', collect()) as $tag)
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-100">{{ $tag->name }}</span>
                            @empty
                                <span class="text-sm text-slate-500">Belum ada tag tema.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        @if ($galleryImages->count() > 1)
            <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($galleryImages as $image)
                    <figure class="overflow-hidden rounded-[24px] border border-white/10 bg-white shadow-lg">
                        <img src="{{ $image['src'] }}" alt="{{ $image['alt'] }}" class="h-40 w-full object-cover sm:h-44">
                    </figure>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="bg-[#f8fafc] py-12 sm:py-14">
    <div class="mx-auto grid max-w-[1920px] gap-6 px-4 sm:px-6 lg:grid-cols-[minmax(0,1.18fr)_360px] lg:px-8">
        <main class="space-y-6">
            <article class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-teal-50 text-teal-700">1</span>
                    <h2 class="text-xl font-bold text-slate-900">Informasi Paket Wisata</h2>
                </div>

                <div class="mt-5 space-y-4 text-sm leading-7 text-slate-600">
                    <p>{{ $product->short_desc ?: 'Tidak ada ringkasan paket yang tersedia.' }}</p>
                    <p>{!! nl2br(e($product->description ?: 'Deskripsi lengkap paket belum diisi.')) !!}</p>
                </div>

                <div class="mt-6 grid gap-3 md:grid-cols-3">
                    @foreach ([
                        ['label' => 'Minimal Peserta', 'value' => $product->min_pax . ' orang'],
                        ['label' => 'Maksimal Peserta', 'value' => $product->max_pax . ' orang'],
                        ['label' => 'Jam Operasional', 'value' => $product->duration_hours ? number_format((float) $product->duration_hours, 1) . ' jam' : 'Mengikuti paket'],
                    ] as $fact)
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">{{ $fact['label'] }}</p>
                            <p class="mt-2 text-lg font-bold text-slate-900">{{ $fact['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">2</span>
                    <h2 class="text-xl font-bold text-slate-900">Fasilitas dan Highlight</h2>
                </div>

                <div class="mt-5 space-y-5">
                    @foreach (['facility' => 'Fasilitas', 'audience' => 'Target Wisatawan', 'theme' => 'Nuansa Paket'] as $key => $label)
                        @if ($tagGroups->get($key, collect())->isNotEmpty())
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $label }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($tagGroups->get($key) as $tag)
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </article>

            <article class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-sky-50 text-sky-700">3</span>
                        <h2 class="text-xl font-bold text-slate-900">Ulasan Tamu</h2>
                    </div>
                    <div class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
                        {{ $reviewCount }} ulasan publik
                    </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-[220px_minmax(0,1fr)]">
                    <div class="rounded-[24px] bg-slate-50 p-5 text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Rating Rata-rata</p>
                        <div class="mt-3 text-5xl font-black text-teal-700">{{ number_format($ratingValue, 1) }}</div>
                        <div class="mt-3 flex items-center justify-center gap-1 text-amber-400" aria-hidden="true">
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= round($ratingValue) ? 'text-amber-400' : 'text-slate-200' }}">&#9733;</span>
                            @endfor
                        </div>
                        <p class="mt-2 text-sm text-slate-500">
                            {{ $reviewCount > 0 ? 'Berdasarkan ulasan publik.' : 'Belum ada ulasan yang dipublikasikan.' }}
                        </p>
                    </div>

                    <div class="space-y-4">
                        @forelse ($recentReviews as $review)
                            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $review->user_name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ \Carbon\Carbon::parse($review->created_at)->translatedFormat('d F Y') }}</p>
                                    </div>
                                    <div class="flex items-center gap-1 text-amber-400" aria-hidden="true">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200' }}">&#9733;</span>
                                        @endfor
                                    </div>
                                </div>

                                @if ($review->comment)
                                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $review->comment }}</p>
                                @else
                                    <p class="mt-3 text-sm leading-6 text-slate-500">Ulasan tanpa komentar.</p>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                                <p class="text-sm font-semibold text-slate-900">Belum ada ulasan yang dipublikasikan.</p>
                                <p class="mt-2 text-sm text-slate-500">Setelah tamu selesai berwisata, ulasan mereka akan tampil di sini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </article>
        </main>

        <aside class="space-y-6">
            <article class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">4</span>
                    <h2 class="text-xl font-bold text-slate-900">Biaya Parkir</h2>
                </div>

                <div class="mt-4 rounded-[24px] border border-emerald-100 bg-emerald-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Informasi Penting</p>
                    <div class="mt-3 space-y-2 text-sm leading-6 text-emerald-900">
                        <p>Biaya parkir <strong>belum termasuk</strong> harga paket wisata dan dibayarkan terpisah di area lokasi.</p>
                        <p>Tarif berikut adalah <strong>estimasi per kendaraan untuk satu kali kunjungan</strong>.</p>                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach ($parkingRates as $rate)
                        <div class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900">{{ $rate['vehicle'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $rate['note'] }}</p>
                            </div>
                            <div class="rounded-2xl bg-white px-3 py-2 text-left sm:text-right">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Tarif Parkir</p>
                                <p class="mt-1 text-sm font-bold text-teal-700">Rp {{ number_format($rate['price'], 0, ',', '.') }}</p>
                                <p class="text-xs text-slate-500">per kendaraan</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 rounded-3xl bg-amber-50 p-4 text-sm leading-6 text-amber-900">
                    Siapkan uang tunai kecil untuk parkir dan gunakan tarif di atas sebagai panduan awal.
                </div>
            </article>

            <article class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Langkah Berikutnya</p>
                <div class="mt-4 space-y-3">
                    <a href="{{ $bookingUrl }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-teal-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">
                        Booking Paket Ini
                    </a>
                    <a href="{{ route('frontend.wisata') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-teal-300 hover:text-teal-700">
                        Jelajahi Paket Lain
                    </a>
                </div>
            </article>
        </aside>
    </div>
</section>
@endsection
