@extends('frontend.layouts.main')

@section('title', 'CapolagaGo - Experience Nature Adventure')
@section('meta_description', 'Platform wisata alam Capolaga untuk menemukan paket glamping, camping, homestay, dan add-on activity terbaik.')

@section('content')
@include('frontend.layouts.header')

@php
    $startingProduct = $heroProducts->first() ?? $featuredProducts->first();
    $today = now()->format('Y-m-d');
    $featuredRatings = $featuredProducts->filter(fn ($product) => (float) $product->rating_avg > 0);
    $averageRating = $featuredRatings->isNotEmpty()
        ? number_format((float) $featuredRatings->avg('rating_avg'), 1)
        : '4.9';
    $featuredImageFor = static function ($product) {
        $storedImage = (string) ($product->primary_image_url ?? '');
        if ($storedImage !== '') {
            return $storedImage;
        }

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

    $featuredDisplayRating = static function ($product) {
        $rating = (float) $product->rating_avg;
        if ($rating > 0) {
            return number_format($rating, 1);
        }

        return match ((string) $product->slug) {
            'glamping-riverside-luxury' => '4.9',
            'standard-camping-ground' => '4.7',
            'homestay-forest-view' => '4.8',
            'rafting-ciater-adventure' => '5',
            default => '4.8',
        };
    };
@endphp

<section id="home" class="pt-16 sm:pt-20 md:pt-24">
    <div class="relative overflow-hidden py-12 sm:py-14 md:py-16 lg:py-20" style="background:linear-gradient(135deg, #1a3a4a 0%, #2d5a6b 50%, #3d7a8a 100%)">
        <div class="mx-auto max-w-[1920px] px-4 md:px-8">
            <div class="flex flex-col gap-6 sm:gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-xl">
                    <h1 class="mb-4 text-[2.1rem] font-bold leading-[1.05] text-white sm:text-4xl lg:text-5xl">
                        Experience Nature Adventure<br />at Capolaga
                    </h1>
                    <p class="mb-6 max-w-lg text-sm leading-7 text-white/80 sm:text-base md:text-lg">
                        Pesan camping, glamping &amp; aktivitas petualangan dalam satu platform.
                    </p>
                    <a href="{{ $startingProduct ? route('ticket.booking.product', ['slug' => $startingProduct->slug]) : route('ticket.booking') }}"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-white px-5 py-3 text-sm font-semibold text-[#1a3a4a] transition hover:bg-white/90 sm:px-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="h-4 w-4" aria-hidden="true">
                            <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
                        </svg>
                        Book Now
                    </a>
                </div>

                <div class="flex flex-col items-start gap-4 lg:items-end">
                    <div class="flex flex-wrap items-center gap-2 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="h-5 w-5 fill-yellow-400 text-yellow-400" aria-hidden="true">
                            <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                        <span class="font-semibold">{{ $averageRating }} Rating</span>
                        <span class="text-white/70">&middot;</span>
                        <span class="text-white/80">500+ Wisatawan</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/20 px-3 py-2 text-xs font-medium text-white backdrop-blur-sm sm:px-4 sm:text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="h-4 w-4" aria-hidden="true">
                                <path d="M3.5 21 14 3"></path>
                                <path d="M20.5 21 10 3"></path>
                                <path d="M15.5 21 12 15l-3.5 6"></path>
                                <path d="M2 21h20"></path>
                            </svg>
                            Camping
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/20 px-3 py-2 text-xs font-medium text-white backdrop-blur-sm sm:px-4 sm:text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="h-4 w-4" aria-hidden="true">
                                <path d="M3.5 21 14 3"></path>
                                <path d="M20.5 21 10 3"></path>
                                <path d="M15.5 21 12 15l-3.5 6"></path>
                                <path d="M2 21h20"></path>
                            </svg>
                            Glamping
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/20 px-3 py-2 text-xs font-medium text-white backdrop-blur-sm sm:px-4 sm:text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="h-4 w-4" aria-hidden="true">
                                <path d="m8 3 4 8 5-5 5 15H2L8 3z"></path>
                            </svg>
                            Adventure
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="border-b border-border bg-white">
        <div class="mx-auto max-w-[1920px] px-4 py-4 md:px-8 md:py-5">
            <form action="{{ route('ticket.booking') }}" method="GET" class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(220px,0.9fr)_minmax(220px,0.9fr)_260px] xl:items-end">
                <div class="grid grid-cols-1 gap-4">
                    <label class="block">
                        <span class="mb-1.5 block text-xs uppercase tracking-wide text-muted-foreground">Cari Pengalaman</span>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true">
                                <path d="m21 21-4.34-4.34"></path>
                                <circle cx="11" cy="11" r="8"></circle>
                            </svg>
                            <input type="search" name="q" value="{{ request('q', '') }}" placeholder="Glamping, camping, homestay..."
                                class="h-12 w-full rounded-lg border border-border bg-background py-0 pl-9 pr-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30" />
                        </div>
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <label class="block">
                        <span class="mb-1.5 block text-xs uppercase tracking-wide text-muted-foreground">Tanggal Kunjungan</span>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true">
                                <path d="M8 2v4"></path>
                                <path d="M16 2v4"></path>
                                <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                <path d="M3 10h18"></path>
                            </svg>
                            <input type="date" name="date" min="{{ $today }}" value="{{ request('date', $today) }}"
                                class="h-12 w-full rounded-lg border border-border bg-background py-0 pl-10 pr-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30" />
                        </div>
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <label class="block">
                        <span class="mb-1.5 block text-xs uppercase tracking-wide text-muted-foreground">Jumlah Peserta</span>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <input type="number" name="guests" min="1" step="1" value="{{ max(1, (int) request('guests', 2)) }}"
                                class="h-12 w-full rounded-lg border border-border bg-background py-0 pl-10 pr-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30" />
                        </div>
                    </label>
                </div>

                <div class="w-full self-end xl:w-[260px]">
                    <button type="submit"
                        class="flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-[#2d9da8] px-8 text-sm font-semibold text-white transition hover:bg-[#2d9da8]/90">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="h-4 w-4" aria-hidden="true">
                        <path d="m21 21-4.34-4.34"></path>
                        <circle cx="11" cy="11" r="8"></circle>
                    </svg>
                    Cari Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<section id="paket" class="border-b border-slate-200 bg-[#f8fafc] py-12 md:py-14">
    <div class="mx-auto max-w-[1920px] px-4 md:px-8">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="h-5 w-5 text-sky-500" aria-hidden="true">
                    <path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"></path>
                    <path d="M20 2v4"></path>
                    <path d="M22 4h-4"></path>
                    <circle cx="4" cy="20" r="2"></circle>
                </svg>
                <h2 class="text-[20px] font-bold tracking-tight text-slate-900 md:text-[22px]">Featured Experiences</h2>
            </div>
            <a href="{{ route('frontend.wisata') }}" class="inline-flex items-center gap-1 self-end text-sm font-medium text-sky-500 transition hover:text-sky-600 sm:self-auto">
                Lihat Semua
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="h-4 w-4" aria-hidden="true">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </a>
        </div>
        <div class="grid grid-cols-1 gap-4 overflow-x-hidden md:grid-cols-2 xl:grid-cols-4 2xl:gap-5">
            @forelse ($featuredProducts as $product)
                @php
                    $detailUrl = route('frontend.wisata.show', ['slug' => $product->slug]);
                    $bookingUrl = route('ticket.booking.product', ['slug' => $product->slug, 'date' => request('date', $today), 'guests' => request('guests', 2)]);
                @endphp
                <article class="min-w-0 w-full overflow-hidden rounded-[24px] bg-white ring-1 ring-slate-200/70">
                    <a href="{{ $detailUrl }}" class="block">
                        <div class="group/image relative h-[215px] overflow-hidden bg-slate-100 sm:h-[230px] md:h-[260px] xl:h-[305px]">
                            <img src="{{ $featuredImageFor($product) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 ease-out group-hover/image:scale-105" />
                            @if ($loop->first)
                                <span class="absolute left-3 top-3 rounded-md bg-[#ff6a5c] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white">POPULER</span>
                            @endif
                        </div>
                        <div class="p-4 sm:p-5">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-[16px] font-semibold leading-tight text-slate-900">{{ $product->name }}</h3>
                                <div class="flex items-center gap-1 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#facc15" stroke="#facc15" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                                        <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                                    </svg>
                                    <span class="text-[14px] font-semibold text-slate-800">{{ $featuredDisplayRating($product) }}</span>
                                </div>
                            </div>
                            <p class="mt-3 min-h-0 text-[13px] leading-6 text-slate-500 sm:min-h-[52px]">{{ $product->short_desc }}</p>
                            <div class="mt-4 border-t border-slate-200 pt-4 sm:pt-5">
                                <p class="text-[12px] uppercase tracking-wide text-slate-400">Mulai dari</p>
                                <p class="mt-2 text-[22px] font-bold text-sky-500">
                                    Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                                    <span class="text-[14px] font-medium text-slate-500">{{ $product->price_label }}</span>
                                </p>
                            </div>
                        </div>
                    </a>
                    <div class="flex flex-col gap-2 border-t border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <a href="{{ $detailUrl }}" class="inline-flex w-full items-center justify-center rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-sky-300 hover:text-sky-700 sm:w-auto">
                            Detail
                        </a>
                        <a href="{{ $bookingUrl }}" class="inline-flex w-full items-center justify-center rounded-md bg-[#2d9da8] px-3 py-2 text-sm font-semibold text-white transition hover:bg-[#2d9da8]/90 sm:w-auto">
                            Booking
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                    Produk unggulan belum tersedia.
                </div>
            @endforelse
        </div>
    </div>
</section>

<section id="addon" class="border-b border-slate-200 bg-white py-7 md:py-8">
    <div class="mx-auto max-w-[1920px] px-4 md:px-8">
        <div class="mb-3 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="h-4 w-4 text-sky-500" aria-hidden="true">
                <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                <circle cx="12" cy="10" r="3"></circle>
            </svg>
            <h2 class="text-[20px] font-bold tracking-tight text-slate-900 md:text-[22px]">Explore Around Capolaga</h2>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($addonProducts->take(4)->values() as $index => $product)
                @php
                    $iconStyles = [
                        ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => 'waves'],
                        ['bg' => 'bg-orange-50', 'text' => 'text-orange-500', 'icon' => 'bike'],
                        ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-600', 'icon' => 'wind'],
                        ['bg' => 'bg-teal-50', 'text' => 'text-teal-600', 'icon' => 'droplets'],
                    ];
                    $style = $iconStyles[$index] ?? $iconStyles[0];
                @endphp

                <div
                    class="flex items-start gap-3 rounded-[18px] border border-slate-200 bg-white px-4 py-4 opacity-80 sm:items-center sm:gap-4 sm:px-5"
                    aria-disabled="true">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $style['bg'] }} {{ $style['text'] }}">
                        @if ($style['icon'] === 'waves')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                                <path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"></path>
                                <path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"></path>
                                <path d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"></path>
                            </svg>
                        @elseif ($style['icon'] === 'bike')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                                <circle cx="18.5" cy="17.5" r="3.5"></circle>
                                <circle cx="5.5" cy="17.5" r="3.5"></circle>
                                <circle cx="15" cy="5" r="1"></circle>
                                <path d="M12 17.5V14l-3-3 4-3 2 3h2"></path>
                            </svg>
                        @elseif ($style['icon'] === 'wind')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                                <path d="M12.8 19.6A2 2 0 1 0 14 16H2"></path>
                                <path d="M17.5 8a2.5 2.5 0 1 1 2 4H2"></path>
                                <path d="M9.8 4.4A2 2 0 1 1 11 8H2"></path>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                                <path d="M7 16.3c2.2 0 4-1.83 4-4.05 0-1.16-.57-2.26-1.71-3.19S7.29 6.75 7 5.3c-.29 1.45-1.14 2.84-2.29 3.76S3 11.1 3 12.25c0 2.22 1.8 4.05 4 4.05z"></path>
                                <path d="M12.56 6.6A10.97 10.97 0 0 0 14 3.02c.5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a6.98 6.98 0 0 1-11.91 4.97"></path>
                            </svg>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <h3 class="text-[15px] font-semibold leading-tight text-slate-900 sm:truncate">{{ $product->name }}</h3>
                        <p class="mt-0.5 text-[13px] font-medium text-sky-500">
                            Rp {{ number_format((float) $product->price, 0, ',', '.') }}{{ $product->price_label }}
                        </p>
                        <p class="mt-1 text-[12px] text-slate-500">
                            <!-- Pilih produk utama dulu untuk menambahkan add-on ini saat booking. -->
                        </p>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-[#f8fafc] p-10 text-center text-sm text-slate-500">
                    Add-on activity belum tersedia.
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
