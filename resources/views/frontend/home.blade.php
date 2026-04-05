@extends('frontend.layouts.main')

@section('content')
@include('frontend.layouts.header')

<section id="home" class="pt-20 md:pt-24">
    <div class="relative overflow-hidden py-14 md:py-16 lg:py-20" style="background:linear-gradient(135deg, #1a3a4a 0%, #2d5a6b 50%, #3d7a8a 100%)">
        <div class="container mx-auto px-4">
            <div class="flex flex-col gap-8">
                <div class="max-w-xl">
                    <h1 class="mb-4 text-3xl font-bold leading-tight text-white md:text-4xl lg:text-5xl">
                        Experience Nature Adventure<br />at Capolaga
                    </h1>
                    <p class="mb-6 text-base text-white/80 md:text-lg">
                        Pesan camping, glamping, homestay, dan aktivitas petualangan dari katalog Capolaga yang sekarang langsung terhubung ke data produk.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a class="inline-flex items-center justify-center rounded-md bg-white px-6 py-3 text-sm font-semibold text-[#1a3a4a] transition hover:bg-white/90"
                            href="{{ route('ticket.booking') }}">
                            Book Now
                        </a>
                        @if ($heroProducts->isNotEmpty())
                            <a class="inline-flex items-center justify-center rounded-md border border-white/30 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                                href="{{ route('ticket.booking', ['product' => $heroProducts->first()->slug]) }}">
                                Mulai Dari {{ $heroProducts->first()->name }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="border-b border-slate-900/15 bg-white">
        <div class="container mx-auto px-4 py-4 md:py-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-end">
                <div class="grid flex-1 grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wide text-muted-foreground">Cari Pengalaman</label>
                        <input type="text" value="{{ $heroProducts->first()?->name ?? '' }}" readonly
                            class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wide text-muted-foreground">Kategori Utama</label>
                        <input type="text" value="{{ $mainCategories->pluck('label')->take(3)->implode(', ') }}" readonly
                            class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wide text-muted-foreground">Add-On Tersedia</label>
                        <input type="text" value="{{ $addonProducts->count() }} aktivitas"
                            readonly class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm" />
                    </div>
                </div>
                <a class="inline-flex items-center justify-center rounded-md bg-[#2d9da8] px-8 py-2.5 text-sm font-medium text-white transition hover:bg-[#2d9da8]/90"
                    href="{{ route('ticket.booking') }}">
                    Cari Sekarang
                </a>
            </div>
        </div>
    </div>
</section>

<section id="paket" class="scroll-mt-24 border-b border-slate-900/15 bg-background py-12 md:py-16">
    <div class="container mx-auto px-4">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-primary">Featured Experiences</p>
                <h2 class="mt-2 text-2xl font-bold text-foreground">Produk unggulan yang aktif di sistem</h2>
            </div>
            <a class="text-sm font-medium text-primary transition-colors hover:text-primary/80"
                href="{{ route('ticket.booking') }}">
                Lihat Semua
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
            @forelse ($featuredProducts as $product)
                <a class="group overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-all duration-300 hover:shadow-lg"
                    href="{{ route('ticket.booking', ['product' => $product->slug]) }}">
                    <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                        @if ($product->primary_image_url)
                            <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                        @else
                            <div class="flex h-full items-center justify-center text-sm text-slate-500">Belum ada gambar</div>
                        @endif
                        @if ($product->is_featured)
                            <div class="absolute left-3 top-3">
                                <span class="rounded-md bg-[#e85a4f] px-2.5 py-1 text-xs font-bold text-white">POPULER</span>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="mb-2 flex items-start justify-between gap-2">
                            <h3 class="text-sm font-semibold leading-tight text-card-foreground">{{ $product->name }}</h3>
                            <span class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-600">
                                {{ $product->category?->label ?? 'Produk' }}
                            </span>
                        </div>
                        <p class="mb-4 text-xs text-muted-foreground">{{ $product->short_desc }}</p>
                        <div class="border-t border-border pt-3">
                            <span class="text-xs uppercase text-muted-foreground">Mulai dari</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-lg font-bold text-primary">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</span>
                                <span class="text-sm text-muted-foreground">{{ $product->price_label }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
                    Belum ada produk unggulan aktif.
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="border-t border-b border-slate-900/15 bg-white py-12 md:py-16">
    <div class="container mx-auto px-4">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-[#20A678]">Kategori Utama</p>
            <h2 class="mt-2 text-2xl font-bold text-[#0f172a]">Pilih gaya menginap dan aktivitas inti</h2>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            @foreach ($mainCategories as $category)
                <a href="{{ route('ticket.booking') }}"
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-1 hover:border-[#20A678] hover:bg-white hover:shadow-lg">
                    <div class="mb-3 h-3 w-16 rounded-full" style="background-color: {{ $category->color_hex }}"></div>
                    <h3 class="text-lg font-bold text-slate-900">{{ $category->label }}</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ ucfirst($category->type) }} experience yang sudah masuk katalog Capolaga.
                    </p>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section id="addon" class="scroll-mt-24 border-t border-b border-slate-900/15 bg-[#f8fafc] py-12 md:py-20">
    <div class="container mx-auto px-4">
        <div class="mb-8">
            <div class="mb-3 inline-flex items-center gap-3 rounded-full border border-teal-200 bg-[#e6f4f1] px-4 py-2 text-[#20A678]">
                <span class="text-sm font-semibold uppercase tracking-wide">Add-On Activity</span>
            </div>
            <h2 class="text-3xl font-bold tracking-tight text-[#0f172a] md:text-4xl">Explore Around Capolaga</h2>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            @forelse ($addonProducts as $product)
                <a class="group flex flex-col rounded-2xl border border-[#e2e8f0] bg-white p-5 transition-all duration-300 hover:-translate-y-1 hover:border-[#20A678] hover:shadow-lg"
                    href="{{ route('ticket.booking', ['product' => $product->slug]) }}">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                        <span class="text-xs font-bold uppercase">{{ substr($product->category?->label ?? 'A', 0, 2) }}</span>
                    </div>
                    <div>
                        <h3 class="mb-1 text-base font-bold text-[#0f172a]">{{ $product->name }}</h3>
                        <p class="text-sm font-semibold text-[#20A678]">
                            Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                            <span class="text-xs font-normal text-[#64748b]">{{ $product->price_label }}</span>
                        </p>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
                    Add-on activity belum tersedia.
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
