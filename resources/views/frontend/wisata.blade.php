@extends('frontend.layouts.main')

@section('title', 'CapolagaGo - Paket Wisata')
@section('meta_description', 'Jelajahi semua paket wisata aktif Capolaga, dari glamping, camping, sampai homestay yang terhubung langsung ke database.')

@section('content')
@include('frontend.layouts.header')

@php
    $today = now()->format('Y-m-d');
@endphp

<section class="bg-gradient-to-r from-teal-700 to-teal-600 pt-24 pb-12 text-white">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold md:text-4xl">Temukan Pengalaman Terbaik</h1>
        <p class="mt-3 max-w-2xl text-lg text-teal-100">Pilih dari berbagai pilihan akomodasi untuk petualangan alam Anda.</p>

        <form action="{{ route('frontend.wisata') }}" method="GET" class="mt-8 flex flex-col gap-3 md:flex-row">
            <div class="flex-1">
                <input type="search" name="q" value="{{ $searchQuery }}" placeholder="Cari glamping, camping, homestay..." class="h-12 w-full rounded-lg border-0 bg-white px-4 text-sm text-slate-900 outline-none" />
            </div>
            <input type="hidden" name="date" value="{{ request('date', $today) }}" />
            <input type="hidden" name="guests" value="{{ request('guests', 2) }}" />
            <button type="submit" class="inline-flex h-12 items-center justify-center rounded-lg bg-white px-6 text-sm font-semibold text-teal-700 transition hover:bg-teal-50">Filter</button>
        </form>
    </div>
</section>

<section class="border-b border-slate-200 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex gap-2 overflow-x-auto py-3">
            <a href="{{ route('frontend.wisata', array_filter(['q' => $searchQuery, 'sort' => $selectedSort, 'date' => request('date'), 'guests' => request('guests')])) }}"
                class="flex-shrink-0 rounded-md px-4 py-2 text-sm font-medium {{ $selectedCategory === 'all' ? 'bg-teal-600 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-teal-50 hover:text-teal-700' }}">
                Semua
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('frontend.wisata', array_filter(['q' => $searchQuery, 'category' => $category->slug, 'sort' => $selectedSort, 'date' => request('date'), 'guests' => request('guests')])) }}"
                    class="flex-shrink-0 rounded-md px-4 py-2 text-sm font-medium {{ $selectedCategory === $category->slug ? 'bg-teal-600 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-teal-50 hover:text-teal-700' }}">
                    {{ $category->label }}
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="py-10 md:py-12">
    <div class="container mx-auto px-4">
        <div class="mb-6 flex items-center justify-between gap-4">
            <p class="text-slate-500">Menampilkan <span class="font-semibold text-slate-900">{{ $products->count() }}</span> hasil</p>
            <form action="{{ route('frontend.wisata') }}" method="GET">
                <input type="hidden" name="q" value="{{ $searchQuery }}" />
                <input type="hidden" name="category" value="{{ $selectedCategory }}" />
                <input type="hidden" name="date" value="{{ request('date', $today) }}" />
                <input type="hidden" name="guests" value="{{ request('guests', 2) }}" />
                <select name="sort" onchange="this.form.submit()" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                    <option value="popular" @selected($selectedSort === 'popular')>Urutkan: Populer</option>
                    <option value="price_low" @selected($selectedSort === 'price_low')>Harga: Rendah ke Tinggi</option>
                    <option value="price_high" @selected($selectedSort === 'price_high')>Harga: Tinggi ke Rendah</option>
                    <option value="rating" @selected($selectedSort === 'rating')>Rating Tertinggi</option>
                </select>
            </form>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($products as $product)
                <article class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                        @if ($product->primary_image_url)
                            <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                        @else
                            <div class="flex h-full items-center justify-center text-sm text-slate-500">Belum ada gambar</div>
                        @endif
                        <span class="absolute left-3 top-3 rounded-md bg-white/90 px-2.5 py-1 text-xs font-semibold capitalize text-slate-800 backdrop-blur-sm">{{ $product->category?->slug ?? 'produk' }}</span>
                        @if ($product->is_featured)
                            <span class="absolute right-12 top-3 rounded-md bg-red-500 px-2.5 py-1 text-xs font-semibold text-white">POPULER</span>
                        @endif
                        <div class="absolute right-3 top-3 rounded-full bg-white/90 px-2 py-1 text-sm font-semibold text-slate-800 backdrop-blur-sm">
                            {{ number_format((float) ($product->rating_avg ?: 4.7), 1) }}
                        </div>
                        <div class="absolute bottom-3 left-3 rounded-lg bg-white/90 px-3 py-1.5 backdrop-blur-sm">
                            <span class="text-lg font-bold text-teal-600">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</span>
                            <span class="text-sm text-slate-500">{{ $product->price_label }}</span>
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-slate-900">{{ $product->name }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $product->short_desc }}</p>
                        <div class="mt-4 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-500">{{ $product->category?->label ?? 'Produk' }}</span>
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-500">{{ $product->min_pax }}-{{ $product->max_pax }} Orang</span>
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-500">{{ $product->price_label }}</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-slate-200 pt-4">
                            <span class="text-sm text-slate-500">{{ $product->category?->label ?? 'Produk' }}</span>
                            <a href="{{ route('ticket.booking', ['product' => $product->slug, 'date' => request('date', $today), 'guests' => request('guests', 2)]) }}" class="inline-flex items-center rounded-md bg-teal-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-teal-700">
                                Pesan Sekarang
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                    Tidak ada paket wisata yang cocok dengan filter saat ini.
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
