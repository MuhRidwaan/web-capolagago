@extends('frontend.layouts.main')

@section('title', 'CapolagaGo - Booking Flow')
@section('meta_description', 'Halaman booking CapolagaGo untuk memilih produk, add-on, checkout, dan konfirmasi pembayaran.')

@section('content')
@if ($midtransClientKey !== '')
    <script type="text/javascript"
        src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ $midtransClientKey }}"></script>
@endif

@php
    $mainProductPayload = $mainProducts->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'category' => $product->category?->slug,
            'category_label' => $product->category?->label ?? 'Produk',
            'description' => $product->short_desc,
            'price' => (float) $product->price,
            'price_label' => $product->price_label,
            'image' => $product->primary_image_url,
            'featured' => (bool) $product->is_featured,
            'min_pax' => (int) $product->min_pax,
            'max_pax' => (int) $product->max_pax,
        ];
    })->values();

    $addonProductPayload = $addonProducts->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'category_label' => $product->category?->label ?? 'Add-on',
            'description' => $product->short_desc,
            'price' => (float) $product->price,
            'price_label' => $product->price_label,
            'image' => $product->primary_image_url,
            'min_pax' => (int) $product->min_pax,
            'max_pax' => (int) $product->max_pax,
        ];
    })->values();

    $paymentMethodPayload = $paymentMethods->map(function ($method) {
        return [
            'id' => $method->id,
            'name' => $method->name,
            'provider' => $method->provider,
            'type' => $method->type,
        ];
    })->values();

    $searchResultCount = $mainProducts->count() + $addonProducts->count();
@endphp

@include('frontend.layouts.header')

<section class="border-b border-slate-200 bg-white pt-24">
    <div class="mx-auto max-w-[1440px] px-4 py-6 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <a href="{{ route('frontend.home') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 transition hover:text-slate-900">
                <span aria-hidden="true">&larr;</span>
                <span>Kembali ke beranda</span>
            </a>
            <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Booking yang sederhana dan jelas</h1>
            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600 sm:text-base">
                Pilih akomodasi, tambahkan activity pelengkap, lalu selesaikan checkout dengan flow yang langsung tersambung ke backend.
            </p>
            @if ($searchQuery !== '')
                <p class="mt-4 inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-800">
                    Hasil pencarian untuk "{{ $searchQuery }}": {{ $searchResultCount }} item
                </p>
            @endif
            @if (!empty($preselectedCategorySlug) && $preselectedCategorySlug !== 'all')
                <p class="mt-4 inline-flex rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
                    Filter kategori aktif: {{ $mainCategories->firstWhere('slug', $preselectedCategorySlug)?->label ?? $preselectedCategorySlug }}
                </p>
            @endif
        </div>
    </div>
</section>

<section class="bg-[#f8fafc] pb-12 pt-6 md:pt-8">
    <div class="mx-auto grid max-w-[1440px] gap-6 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:px-8">
        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-step-indicator="1" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-900">1. Pilih Produk</button>
                    <button type="button" data-step-indicator="2" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-500">2. Add-on</button>
                    <button type="button" data-step-indicator="3" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-500">3. Keranjang</button>
                    <button type="button" data-step-indicator="4" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-500">4. Checkout</button>
                    <button type="button" data-step-indicator="5" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-500">5. Konfirmasi</button>
                </div>
                <div id="flow-feedback" class="hidden"></div>
            </section>

            <section data-booking-step="1" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Step 1</p>
                        <h2 class="mt-2 text-2xl font-bold text-slate-900">Pilih produk utama</h2>
                        <p class="mt-1 text-sm text-slate-600">Daftar produk utama di-render dari kategori internal yang aktif.</p>
                    </div>
                    <label class="block">
                        <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Filter</span>
                        <select id="category-filter" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:bg-white">
                            <option value="all">Semua kategori</option>
                            @foreach ($mainCategories as $category)
                                <option value="{{ $category->slug }}">{{ $category->label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div id="product-grid" class="grid gap-4 xl:grid-cols-2"></div>

                <div class="mt-6 flex items-center justify-end">
                    <button type="button" id="step-1-next" class="rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                        Lanjut ke Add-on
                    </button>
                </div>
            </section>

            <section data-booking-step="2" class="hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Step 2</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Tambahkan pengalaman pelengkap</h2>
                    <p class="mt-1 text-sm text-slate-600">Daftar add-on berikut berasal dari kategori `addon` yang aktif di backend.</p>
                </div>

                <div id="addon-grid" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"></div>

                <div class="mt-6 flex items-center justify-between gap-3">
                    <button type="button" data-step-prev="1" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                        Kembali
                    </button>
                    <button type="button" data-step-next="3" class="rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                        Lanjut ke Keranjang
                    </button>
                </div>
            </section>

            <section data-booking-step="3" class="hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Step 3</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Periksa keranjang</h2>
                    <p class="mt-1 text-sm text-slate-600">Ringkasan ini sudah mengikuti pilihan produk yang dipilih di browser.</p>
                </div>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_280px]">
                    <div class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Produk Dipilih</p>
                            <div id="cart-main" class="mt-3 text-sm text-slate-600">Belum ada produk dipilih.</div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Add-on Activity</p>
                                <span id="addon-count" class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600">0 dipilih</span>
                            </div>
                            <div id="cart-addons" class="mt-3 space-y-3 text-sm text-slate-600">Belum ada add-on dipilih.</div>
                        </div>
                    </div>

                    <aside class="rounded-2xl border border-slate-200 bg-gradient-to-b from-slate-50 to-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Estimasi Harga</p>
                        <div class="mt-4 space-y-3 text-sm text-slate-700">
                            <div class="flex items-center justify-between">
                                <span>Paket utama</span>
                                <span id="summary-base">Rp 0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Add-on</span>
                                <span id="summary-addon">Rp 0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Kuantitas</span>
                                <span id="summary-quantity">0 item</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Service fee</span>
                                <span id="summary-fee">Rp 0</span>
                            </div>
                            <div class="border-t border-slate-200 pt-3">
                                <div class="flex items-center justify-between text-base font-bold text-slate-900">
                                    <span>Total</span>
                                    <span id="summary-total">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="mt-6 flex items-center justify-between gap-3">
                    <button type="button" data-step-prev="2" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                        Kembali
                    </button>
                    <button type="button" data-step-next="4" class="rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                        Lanjut ke Checkout
                    </button>
                </div>
            </section>

            <section data-booking-step="4" class="hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Step 4</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Checkout customer</h2>
                    <p class="mt-1 text-sm text-slate-600">Dropdown metode pembayaran sekarang ditarik langsung dari tabel payment method aktif.</p>
                </div>

                <form id="checkout-form" class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
                    @csrf
                    <div class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">Nama lengkap</span>
                                <input name="name" type="text" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400" placeholder="Nama customer" />
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">No. WhatsApp</span>
                                <input name="phone" type="tel" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400" placeholder="08xxxxxxxxxx" />
                            </label>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">Email</span>
                                <input name="email" type="email" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400" placeholder="email@contoh.com" />
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">Tanggal kunjungan</span>
                                <input id="checkout-date-input" name="date" type="date" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400" />
                            </label>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">Jumlah peserta</span>
                                <input id="guest-input" name="guests" type="number" min="1" value="2" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400" />
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">Metode pembayaran</span>
                                <select id="payment-method-select" name="payment_method_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400">
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }} ({{ strtoupper($method->type) }})</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">Catatan tambahan</span>
                            <textarea name="notes" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400" placeholder="Contoh: late check-in, butuh area dekat sungai, dsb."></textarea>
                        </label>
                    </div>

                    <aside class="rounded-2xl border border-slate-200 bg-gradient-to-b from-slate-50 to-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Ringkasan Pembayaran</p>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Paket</span>
                                <span id="checkout-base">Rp 0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Add-on</span>
                                <span id="checkout-addon">Rp 0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Kuantitas</span>
                                <span id="checkout-quantity">0 item</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Service fee</span>
                                <span id="checkout-fee">Rp 0</span>
                            </div>
                            <div class="border-t border-slate-200 pt-3">
                                <div class="flex items-center justify-between text-base font-bold">
                                    <span>Total bayar</span>
                                    <span id="checkout-total">Rp 0</span>
                                </div>
                            </div>
                            <p id="payment-method-helper" class="rounded-xl bg-white px-3 py-2 text-xs text-slate-500"></p>
                            <div id="payment-gateway-result" class="hidden rounded-xl bg-white px-3 py-3 text-xs text-slate-500"></div>
                            <button type="button" data-step-prev="3" class="w-full rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                                Kembali ke Keranjang
                            </button>
                            <button type="submit" id="checkout-submit-button" class="w-full rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                                Buat Booking Sekarang
                            </button>
                        </div>
                    </aside>
                </form>
            </section>

            <section data-booking-step="5" class="hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mx-auto max-w-2xl text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-2xl font-bold text-emerald-700">OK</div>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Step 5</p>
                    <h2 class="mt-2 text-3xl font-bold text-slate-900">Booking berhasil dibuat</h2>
                    <p id="confirmation-message" class="mt-3 text-sm leading-6 text-slate-600">
                        Booking sudah tersimpan. Kamu bisa lanjut ke halaman status booking untuk memantau pembayaran.
                    </p>

                    <div class="mt-6 grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-left sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Kode booking</p>
                            <p id="confirmation-booking-code" class="mt-2 text-sm font-semibold text-slate-900">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Total</p>
                            <p id="confirmation-total" class="mt-2 text-sm font-semibold text-slate-900">Rp 0</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Produk</p>
                            <p id="confirmation-product" class="mt-2 text-sm font-semibold text-slate-900">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Atas nama</p>
                            <p id="confirmation-name" class="mt-2 text-sm font-semibold text-slate-900">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Kontak</p>
                            <p id="confirmation-contact" class="mt-2 text-sm font-semibold text-slate-900">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Tanggal kunjungan</p>
                            <p id="confirmation-date" class="mt-2 text-sm font-semibold text-slate-900">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Jumlah peserta</p>
                            <p id="confirmation-guests" class="mt-2 text-sm font-semibold text-slate-900">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Metode pembayaran</p>
                            <p id="confirmation-payment" class="mt-2 text-sm font-semibold text-slate-900">-</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                        <a id="confirmation-status-link" href="{{ route('ticket.booking') }}" class="rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                            Lihat Status Booking
                        </a>
                        <button id="restart-booking" type="button" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Buat Booking Baru
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <aside class="h-fit rounded-3xl border border-slate-200 bg-white p-4 shadow-sm lg:sticky lg:top-24">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Ringkasan Live</p>
                    <h2 class="mt-1 text-base font-bold text-slate-900">Booking customer</h2>
                </div>
                <span id="sidebar-addon-count" class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">0 add-on</span>
            </div>

            <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Kunjungan</p>
                <div class="mt-3 grid gap-2">
                    <input id="trip-date-input" type="date" readonly class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm text-slate-700 outline-none" />
                    <input id="trip-guests-input" type="number" min="1" value="2" readonly class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 outline-none" />
                    <div class="flex items-center justify-between gap-2 text-sm text-slate-700">
                        <p id="sidebar-visit-date" class="rounded-full bg-white px-3 py-2 font-semibold text-slate-900">Belum dipilih</p>
                        <p id="sidebar-visit-guests" class="rounded-full bg-white px-3 py-2 font-semibold text-slate-900">2 orang</p>
                    </div>
                </div>
            </div>

            <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Paket</p>
                <div id="sidebar-main" class="mt-2 text-sm text-slate-600">Belum ada produk dipilih.</div>
            </div>

            <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Add-on</p>
                <div id="sidebar-addons" class="mt-2 text-sm text-slate-600">Belum ada add-on dipilih.</div>
            </div>

            <div class="mt-3 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 p-4 text-white">
                <div class="flex items-center justify-between text-sm text-slate-300">
                    <span>Estimasi subtotal</span>
                    <span id="sidebar-subtotal">Rp 0</span>
                </div>
                <div class="mt-2 flex items-center justify-between text-sm text-slate-300">
                    <span>Service fee</span>
                    <span id="sidebar-fee">Rp 0</span>
                </div>
                <div class="mt-4 border-t border-white/10 pt-4">
                    <div class="flex items-center justify-between text-base font-bold">
                        <span>Total</span>
                        <span id="sidebar-total">Rp 0</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</section>

<script>
    (() => {
        const mainProducts = @json($mainProductPayload);
        const addonProducts = @json($addonProductPayload);
        const paymentMethods = @json($paymentMethodPayload);
        const preselectedSlug = @json($preselectedProductSlug);
        const preselectedCategorySlug = @json($preselectedCategorySlug);
        const prefilledVisitDate = @json($prefilledVisitDate);
        const prefilledGuests = @json($prefilledGuests);

        const currency = (value) => new Intl.NumberFormat('id-ID').format(value || 0);
        const productGrid = document.getElementById('product-grid');
        const addonGrid = document.getElementById('addon-grid');
        const categoryFilter = document.getElementById('category-filter');
        const paymentMethodSelect = document.getElementById('payment-method-select');
        const paymentMethodHelper = document.getElementById('payment-method-helper');
        const paymentGatewayResult = document.getElementById('payment-gateway-result');
        const flowFeedback = document.getElementById('flow-feedback');
        const checkoutForm = document.getElementById('checkout-form');
        const checkoutSubmitButton = document.getElementById('checkout-submit-button');
        const stepPanels = [...document.querySelectorAll('[data-booking-step]')];
        const stepIndicators = [...document.querySelectorAll('[data-step-indicator]')];
        const stepOneNextButton = document.getElementById('step-1-next');
        const guestInput = document.getElementById('guest-input');
        const tripGuestsInput = document.getElementById('trip-guests-input');
        const checkoutDateInput = document.getElementById('checkout-date-input');
        const tripDateInput = document.getElementById('trip-date-input');
        const confirmationMessage = document.getElementById('confirmation-message');
        const confirmationStatusLink = document.getElementById('confirmation-status-link');
        const csrfToken = checkoutForm.querySelector('input[name="_token"]').value;
        const availabilityUrl = @json(route('ticket.booking.availability'));
        const estimateUrl = @json(route('ticket.booking.estimate'));
        const checkoutUrl = @json(route('ticket.booking.checkout'));

        const state = {
            currentStep: mainProducts.some((product) => product.slug === preselectedSlug) ? 2 : 1,
            selectedProduct: mainProducts.find((product) => product.slug === preselectedSlug) || null,
            selectedAddons: [],
            customer: null,
            lastBooking: null,
            pricing: {
                subtotal: 0,
                addon_total: 0,
                fee_amount: 0,
                total_amount: 0,
                total_quantity: 0,
                loading: false,
            },
        };

        const getToday = () => {
            const now = new Date();
            const offset = now.getTimezoneOffset() * 60000;
            return new Date(now.getTime() - offset).toISOString().slice(0, 10);
        };

        const setFeedback = (message, tone = 'info') => {
            const tones = {
                info: ['border-emerald-200', 'bg-emerald-50', 'text-emerald-900'],
                warning: ['border-amber-200', 'bg-amber-50', 'text-amber-900'],
                danger: ['border-rose-200', 'bg-rose-50', 'text-rose-900'],
            };

            const [borderClass, bgClass, textClass] = tones[tone] || tones.info;
            flowFeedback.className = `mt-3 rounded-xl border px-4 py-3 text-sm ${borderClass} ${bgClass} ${textClass}`;
            flowFeedback.textContent = message;
        };

        const renderGatewayResult = (html, tone = 'info') => {
            const tones = {
                info: 'border border-slate-200 bg-white text-slate-600',
                warning: 'border border-amber-200 bg-amber-50 text-amber-900',
                danger: 'border border-rose-200 bg-rose-50 text-rose-900',
            };

            paymentGatewayResult.className = `rounded-xl px-3 py-3 text-xs ${tones[tone] || tones.info}`;
            paymentGatewayResult.classList.remove('hidden');
            paymentGatewayResult.innerHTML = html;
        };

        const clearGatewayResult = () => {
            paymentGatewayResult.classList.add('hidden');
            paymentGatewayResult.innerHTML = '';
        };

        const updateStepIndicator = () => {
            stepIndicators.forEach((indicator) => {
                const stepNumber = Number(indicator.dataset.stepIndicator);
                const isCurrent = stepNumber === state.currentStep;
                const isCompleted = stepNumber < state.currentStep;

                indicator.className = `rounded-lg border px-4 py-2 text-sm font-semibold transition ${
                    isCurrent
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
                        : isCompleted
                            ? 'border-emerald-100 bg-emerald-50 text-emerald-700'
                            : 'border-slate-200 bg-white text-slate-500'
                }`;
            });
        };

        const showStep = (stepNumber) => {
            state.currentStep = stepNumber;

            stepPanels.forEach((panel) => {
                panel.classList.toggle('hidden', Number(panel.dataset.bookingStep) !== stepNumber);
            });

            updateStepIndicator();
            stepPanels.find((panel) => Number(panel.dataset.bookingStep) === stepNumber)
                ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        const updateStepButtons = () => {
            const canContinueFromStepOne = Boolean(state.selectedProduct);

            stepOneNextButton.disabled = !canContinueFromStepOne;
            stepOneNextButton.textContent = canContinueFromStepOne
                ? 'Lanjut ke Add-on'
                : 'Pilih produk dulu';
        };

        const getGuestCount = () => Number(guestInput.value || 0);

        const isAddonEligible = (addon) => {
            const guests = getGuestCount();

            if (!addon || guests < 1) {
                return false;
            }

            return guests >= Number(addon.min_pax || 0) && guests <= Number(addon.max_pax || Number.MAX_SAFE_INTEGER);
        };

        const syncAddonEligibility = () => {
            const invalidSelectedAddons = state.selectedAddons.filter((addon) => !isAddonEligible(addon));

            if (!invalidSelectedAddons.length) {
                return;
            }

            state.selectedAddons = state.selectedAddons.filter((addon) => isAddonEligible(addon));

            const invalidNames = invalidSelectedAddons.map((addon) => addon.name).join(', ');
            setFeedback(`Beberapa add-on dilepas otomatis karena tidak cocok dengan jumlah tamu saat ini: ${invalidNames}.`, 'warning');
        };

        const renderSummary = () => {
            const basePrice = state.selectedProduct?.price || 0;
            const addonPrice = state.selectedAddons.reduce((sum, item) => sum + item.price, 0);
            const guests = guestInput.value || 2;
            const date = checkoutDateInput.value || '';
            const totalQuantity = state.pricing.total_quantity;
            const feeAmount = state.pricing.fee_amount;
            const totalAmount = state.pricing.total_amount || (basePrice + addonPrice);
            const subtotalAmount = state.pricing.subtotal || (basePrice + addonPrice);

            document.getElementById('cart-main').innerHTML = state.selectedProduct
                ? `<div class="rounded-xl bg-white p-3"><strong>${state.selectedProduct.name}</strong><p class="mt-1 text-xs text-slate-500">${state.selectedProduct.category_label} • Rp ${currency(state.selectedProduct.price)}${state.selectedProduct.price_label}</p></div>`
                : 'Belum ada produk dipilih.';

            document.getElementById('cart-addons').innerHTML = state.selectedAddons.length
                ? state.selectedAddons.map((item) => `<div class="rounded-xl bg-white p-3"><strong>${item.name}</strong><p class="mt-1 text-xs text-slate-500">Rp ${currency(item.price)}${item.price_label}</p></div>`).join('')
                : 'Belum ada add-on dipilih.';

            document.getElementById('addon-count').textContent = `${state.selectedAddons.length} dipilih`;
            document.getElementById('sidebar-addon-count').textContent = `${state.selectedAddons.length} add-on`;

            document.getElementById('summary-base').textContent = `Rp ${currency(basePrice)}`;
            document.getElementById('summary-addon').textContent = `Rp ${currency(addonPrice)}`;
            document.getElementById('summary-quantity').textContent = `${totalQuantity} item`;
            document.getElementById('summary-fee').textContent = `Rp ${currency(feeAmount)}`;
            document.getElementById('summary-total').textContent = `Rp ${currency(totalAmount)}`;

            document.getElementById('checkout-base').textContent = `Rp ${currency(basePrice)}`;
            document.getElementById('checkout-addon').textContent = `Rp ${currency(addonPrice)}`;
            document.getElementById('checkout-quantity').textContent = `${totalQuantity} item`;
            document.getElementById('checkout-fee').textContent = `Rp ${currency(feeAmount)}`;
            document.getElementById('checkout-total').textContent = `Rp ${currency(totalAmount)}`;

            document.getElementById('sidebar-subtotal').textContent = `Rp ${currency(subtotalAmount)}`;
            document.getElementById('sidebar-fee').textContent = `Rp ${currency(feeAmount)}`;
            document.getElementById('sidebar-total').textContent = `Rp ${currency(totalAmount)}`;

            document.getElementById('sidebar-main').innerHTML = state.selectedProduct
                ? `<div class="rounded-xl bg-white p-3"><strong>${state.selectedProduct.name}</strong><p class="mt-1 text-xs text-slate-500">${state.selectedProduct.category_label}</p></div>`
                : 'Belum ada produk dipilih.';

            document.getElementById('sidebar-addons').innerHTML = state.selectedAddons.length
                ? state.selectedAddons.map((item) => `<p class="rounded-xl bg-white px-3 py-2">${item.name}</p>`).join('')
                : 'Belum ada add-on dipilih.';

            document.getElementById('sidebar-visit-guests').textContent = `${guests} orang`;
            tripGuestsInput.value = guests;
            document.getElementById('sidebar-visit-date').textContent = date || 'Belum dipilih';
            tripDateInput.value = date;
            updateStepButtons();
        };

        const renderConfirmation = () => {
            const booking = state.lastBooking;
            const paymentName = booking?.booking?.payment_method
                || paymentMethods.find((method) => String(method.id) === paymentMethodSelect.value)?.name
                || '-';

            document.getElementById('confirmation-booking-code').textContent = booking?.booking?.booking_code || '-';
            document.getElementById('confirmation-total').textContent = `Rp ${currency(booking?.booking?.total_amount || state.pricing.total_amount || 0)}`;
            document.getElementById('confirmation-product').textContent = state.selectedProduct?.name || '-';
            document.getElementById('confirmation-name').textContent = state.customer?.name || '-';
            document.getElementById('confirmation-contact').textContent = state.customer
                ? `${state.customer.phone || '-'} • ${state.customer.email || '-'}`
                : '-';
            document.getElementById('confirmation-date').textContent = state.customer?.date || checkoutDateInput.value || '-';
            document.getElementById('confirmation-guests').textContent = `${state.customer?.guests || guestInput.value || 0} orang`;
            document.getElementById('confirmation-payment').textContent = paymentName;

            if (booking?.redirect_url) {
                confirmationStatusLink.href = booking.redirect_url;
            } else {
                confirmationStatusLink.href = @json(route('ticket.booking'));
            }
        };

        const syncEstimate = async () => {
            if (!state.selectedProduct || !checkoutDateInput.value || !guestInput.value || !paymentMethodSelect.value) {
                state.pricing = {
                    subtotal: 0,
                    addon_total: state.selectedAddons.reduce((sum, item) => sum + item.price, 0),
                    fee_amount: 0,
                    total_amount: 0,
                    total_quantity: 0,
                    loading: false,
                };
                renderSummary();
                return;
            }

            state.pricing.loading = true;

            const params = new URLSearchParams({
                visit_date: checkoutDateInput.value,
                total_guests: guestInput.value,
                payment_method_id: paymentMethodSelect.value,
                main_product_id: String(state.selectedProduct.id),
            });

            state.selectedAddons.forEach((addon) => {
                params.append('addon_ids[]', String(addon.id));
            });

            try {
                const response = await fetch(`${estimateUrl}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json();

                if (!response.ok) {
                    const validationMessage = result.message
                        || Object.values(result.errors || {}).flat().join(' ')
                        || 'Estimasi harga belum bisa dihitung.';
                    throw new Error(validationMessage);
                }

                const estimate = result.estimate;
                state.pricing = {
                    subtotal: Number(estimate.subtotal || 0),
                    addon_total: estimate.items
                        .filter((item) => item.is_addon)
                        .reduce((sum, item) => sum + Number(item.subtotal || 0), 0),
                    fee_amount: Number(estimate.fee_amount || 0),
                    total_amount: Number(estimate.total_amount || 0),
                    total_quantity: estimate.items.reduce((sum, item) => sum + Number(item.quantity || 0), 0),
                    loading: false,
                };
                renderSummary();
            } catch (error) {
                state.pricing.loading = false;
                setFeedback(error.message || 'Estimasi harga belum bisa dihitung dari backend.', 'warning');
            }
        };

        const getProductFallback = (name) => {
            const initials = (name || 'PR')
                .split(' ')
                .slice(0, 2)
                .map((part) => part.charAt(0))
                .join('')
                .toUpperCase();

            return `
                <div class="flex h-full items-center justify-center bg-gradient-to-br from-emerald-100 via-slate-100 to-white text-slate-500">
                    <div class="text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/80 text-lg font-bold text-emerald-700 shadow-sm">${initials}</div>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Belum ada foto</p>
                    </div>
                </div>
            `;
        };

        const renderMainProducts = () => {
            const filterValue = categoryFilter.value;
            const filtered = filterValue === 'all'
                ? mainProducts
                : mainProducts.filter((product) => product.category === filterValue);

            if (!filtered.length) {
                productGrid.innerHTML = `
                    <div class="xl:col-span-2 rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                        Belum ada produk aktif di kategori ini.
                    </div>
                `;
                return;
            }

            productGrid.innerHTML = filtered.map((product) => `
                <button type="button" data-product-slug="${product.slug}" class="text-left rounded-3xl border p-4 shadow-sm transition ${state.selectedProduct?.slug === product.slug ? 'border-emerald-400 bg-emerald-50/70 ring-2 ring-emerald-100' : 'border-slate-200 bg-white hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md'}">
                    <div class="grid gap-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start">
                        <div class="aspect-[4/3] overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
                            ${product.image ? `<img src="${product.image}" alt="${product.name}" class="h-full w-full object-cover" />` : getProductFallback(product.name)}
                        </div>
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">${product.category_label}</p>
                                    <h3 class="mt-2 text-xl font-bold text-slate-900">${product.name}</h3>
                                </div>
                                ${product.featured ? '<span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Featured</span>' : ''}
                            </div>
                            <p class="mt-3 min-h-[52px] text-sm leading-6 text-slate-600">${product.description || 'Deskripsi produk belum tersedia.'}</p>
                            <div class="mt-4 flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Harga</p>
                                    <p class="mt-1 text-lg font-bold text-slate-900">Rp ${currency(product.price)}<span class="text-sm font-medium text-slate-500"> ${product.price_label}</span></p>
                                </div>
                                <span class="rounded-2xl px-4 py-2.5 text-sm font-semibold ${state.selectedProduct?.slug === product.slug ? 'bg-emerald-700 text-white shadow-sm' : 'bg-slate-100 text-slate-700'}">
                                    ${state.selectedProduct?.slug === product.slug ? 'Dipilih' : 'Pilih produk'}
                                </span>
                            </div>
                        </div>
                    </div>
                </button>
            `).join('');

            productGrid.querySelectorAll('[data-product-slug]').forEach((button) => {
                button.addEventListener('click', () => {
                    state.selectedProduct = mainProducts.find((product) => product.slug === button.dataset.productSlug) || null;
                    state.customer = null;
                    state.lastBooking = null;
                    renderMainProducts();
                    renderSummary();
                    renderConfirmation();
                    syncEstimate();
                    checkAvailability();
                    setFeedback('Produk utama sudah dipilih. Kamu bisa lanjut ke tahap add-on.', 'info');
                });
            });
        };

        const renderAddons = () => {
            if (!addonProducts.length) {
                addonGrid.innerHTML = `
                    <div class="md:col-span-2 xl:col-span-3 rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                        Add-on belum tersedia saat ini.
                    </div>
                `;
                return;
            }

            addonGrid.innerHTML = addonProducts.map((product) => {
                const selected = state.selectedAddons.some((item) => item.id === product.id);
                const eligible = isAddonEligible(product);

                return `
                    <button type="button" data-addon-id="${product.id}" ${eligible ? '' : 'disabled'} class="text-left rounded-3xl border p-4 shadow-sm transition ${eligible ? '' : 'cursor-not-allowed opacity-60'} ${selected ? 'border-emerald-400 bg-emerald-50 ring-2 ring-emerald-100' : 'border-slate-200 bg-white'} ${eligible && !selected ? 'hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md' : ''}">
                        <div class="mb-4 aspect-[4/3] overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
                            ${product.image ? `<img src="${product.image}" alt="${product.name}" class="h-full w-full object-cover" />` : getProductFallback(product.name)}
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">${product.category_label}</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-900">${product.name}</h3>
                        <p class="mt-2 min-h-[44px] text-sm leading-6 text-slate-600">${product.description || 'Deskripsi add-on belum tersedia.'}</p>
                        <p class="mt-3 text-xs font-medium ${eligible ? 'text-slate-500' : 'text-amber-700'}">
                            ${eligible
                                ? `Cocok untuk ${product.min_pax}-${product.max_pax} tamu`
                                : `Butuh ${product.min_pax}-${product.max_pax} tamu`}
                        </p>
                        <div class="mt-4 flex items-end justify-between gap-3">
                            <p class="text-base font-bold text-slate-900">Rp ${currency(product.price)}<span class="text-sm font-medium text-slate-500"> ${product.price_label}</span></p>
                            <span class="rounded-2xl px-4 py-2.5 text-sm font-semibold ${selected ? 'bg-emerald-700 text-white shadow-sm' : 'bg-slate-100 text-slate-700'}">
                                ${selected ? 'Dipilih' : eligible ? 'Tambah' : 'Tidak Tersedia'}
                            </span>
                        </div>
                    </button>
                `;
            }).join('');

            addonGrid.querySelectorAll('[data-addon-id]').forEach((button) => {
                button.addEventListener('click', () => {
                    const addonId = Number(button.dataset.addonId);
                    const addon = addonProducts.find((item) => item.id === addonId);

                    if (!addon) {
                        return;
                    }

                    if (!isAddonEligible(addon)) {
                        setFeedback(`Add-on ${addon.name} hanya tersedia untuk ${addon.min_pax}-${addon.max_pax} tamu.`, 'warning');
                        return;
                    }

                    const exists = state.selectedAddons.some((item) => item.id === addonId);
                    state.selectedAddons = exists
                        ? state.selectedAddons.filter((item) => item.id !== addonId)
                        : [...state.selectedAddons, addon];
                    state.customer = null;
                    state.lastBooking = null;

                    renderAddons();
                    renderSummary();
                    renderConfirmation();
                    syncEstimate();
                });
            });
        };

        const checkAvailability = async () => {
            if (!state.selectedProduct || !checkoutDateInput.value || !guestInput.value) {
                return;
            }

            const params = new URLSearchParams({
                product_id: state.selectedProduct.id,
                visit_date: checkoutDateInput.value,
                total_guests: guestInput.value,
            });

            try {
                const response = await fetch(`${availabilityUrl}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json();
                setFeedback(result.message, result.available ? 'info' : 'warning');
            } catch (error) {
                setFeedback('Gagal mengecek ketersediaan produk saat ini.', 'warning');
            }
        };

        const syncPaymentHelper = () => {
            const selectedMethod = paymentMethods.find((method) => String(method.id) === paymentMethodSelect.value);
            paymentMethodHelper.textContent = selectedMethod
                ? `${selectedMethod.name} terdeteksi sebagai metode ${String(selectedMethod.provider || '-').toUpperCase()} / ${String(selectedMethod.type || '-').toUpperCase()}.`
                : 'Pilih metode pembayaran.';
        };

        const submitCheckout = async (event) => {
            event.preventDefault();

            if (!state.selectedProduct) {
                setFeedback('Pilih produk utama terlebih dahulu sebelum checkout.', 'warning');
                return;
            }

            const formData = new FormData(checkoutForm);
            const payload = {
                customer_name: formData.get('name'),
                customer_email: formData.get('email'),
                customer_phone: formData.get('phone'),
                visit_date: formData.get('date'),
                total_guests: Number(formData.get('guests') || 0),
                payment_method_id: Number(formData.get('payment_method_id')),
                main_product_id: state.selectedProduct.id,
                addon_ids: state.selectedAddons.map((item) => item.id),
                notes: formData.get('notes'),
            };

            checkoutSubmitButton.disabled = true;
            checkoutSubmitButton.textContent = 'Memproses booking...';

            try {
                const response = await fetch(checkoutUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const result = await response.json();

                if (!response.ok) {
                    const validationMessage = result.message
                        || Object.values(result.errors || {}).flat().join(' ')
                        || 'Booking gagal dibuat.';
                    throw new Error(validationMessage);
                }

                state.customer = {
                    name: formData.get('name'),
                    phone: formData.get('phone'),
                    email: formData.get('email'),
                    date: formData.get('date'),
                    guests: formData.get('guests'),
                };
                state.lastBooking = result;
                renderConfirmation();
                showStep(5);
                setFeedback(`${result.message} Kode booking: ${result.booking.booking_code}. Total bayar: Rp ${currency(result.booking.total_amount)}.`, 'info');

                if (result.payment_gateway?.provider === 'midtrans') {
                    if (result.payment_gateway.snap_token && window.snap) {
                        confirmationMessage.textContent = 'Booking sudah dibuat. Popup pembayaran Midtrans akan dibuka, lalu kamu akan diarahkan ke halaman status booking.';
                        renderGatewayResult(`Snap token Midtrans berhasil dibuat. Popup pembayaran akan dibuka untuk booking <strong>${result.booking.booking_code}</strong>.`);

                        window.snap.pay(result.payment_gateway.snap_token, {
                            onSuccess: () => {
                                confirmationMessage.textContent = 'Pembayaran Midtrans berhasil. Kamu akan diarahkan ke halaman status booking.';
                                setFeedback(`Pembayaran Midtrans berhasil untuk booking ${result.booking.booking_code}.`, 'info');
                                if (result.redirect_url) {
                                    window.location.href = result.redirect_url;
                                }
                            },
                            onPending: () => {
                                confirmationMessage.textContent = 'Pembayaran Midtrans masih menunggu penyelesaian. Kamu akan diarahkan ke halaman status booking.';
                                setFeedback(`Pembayaran Midtrans sedang menunggu penyelesaian untuk booking ${result.booking.booking_code}.`, 'warning');
                                if (result.redirect_url) {
                                    window.location.href = result.redirect_url;
                                }
                            },
                            onError: () => {
                                confirmationMessage.textContent = 'Terjadi kendala saat membuka pembayaran Midtrans. Kamu bisa lanjut dari halaman status booking.';
                                setFeedback(`Terjadi kendala saat membuka pembayaran Midtrans untuk booking ${result.booking.booking_code}.`, 'danger');
                            },
                            onClose: () => {
                                confirmationMessage.textContent = 'Popup pembayaran Midtrans ditutup. Booking tetap tersimpan dan bisa dilanjutkan dari halaman status booking.';
                                setFeedback(`Popup pembayaran Midtrans ditutup. Booking ${result.booking.booking_code} masih menunggu pembayaran.`, 'warning');
                                if (result.redirect_url) {
                                    window.location.href = result.redirect_url;
                                }
                            },
                        });
                    } else {
                        renderGatewayResult(
                            result.payment_gateway.error
                                ? `Booking berhasil dibuat, tetapi Snap Midtrans belum berhasil disiapkan: ${result.payment_gateway.error}`
                                : `Booking berhasil dibuat. Snap Midtrans belum dapat dibuka untuk booking <strong>${result.booking.booking_code}</strong>.`,
                            result.payment_gateway.error ? 'warning' : 'info'
                        );
                        confirmationMessage.textContent = 'Booking sudah dibuat, tetapi pembayaran Midtrans belum siap dibuka. Silakan cek halaman status booking.';
                        if (result.redirect_url) {
                            window.setTimeout(() => {
                                window.location.href = result.redirect_url;
                            }, 1200);
                        }
                    }
                } else {
                    confirmationMessage.textContent = 'Booking sudah tersimpan ke database dan kamu bisa lanjut memantau pembayarannya di halaman status booking.';
                    renderGatewayResult(`Booking <strong>${result.booking.booking_code}</strong> berhasil dibuat dengan metode <strong>${result.booking.payment_method}</strong>. Status saat ini: menunggu pembayaran.`, 'info');
                    if (result.redirect_url) {
                        window.setTimeout(() => {
                            window.location.href = result.redirect_url;
                        }, 1200);
                    }
                }
            } catch (error) {
                setFeedback(error.message || 'Terjadi kesalahan saat membuat booking.', 'danger');
                renderGatewayResult(error.message || 'Terjadi kesalahan saat membuat booking.', 'danger');
            } finally {
                checkoutSubmitButton.disabled = false;
                checkoutSubmitButton.textContent = 'Buat Booking Sekarang';
            }
        };

        categoryFilter.addEventListener('change', renderMainProducts);
        paymentMethodSelect.addEventListener('change', syncPaymentHelper);
        paymentMethodSelect.addEventListener('change', syncEstimate);
        stepOneNextButton.addEventListener('click', () => {
            if (!state.selectedProduct) {
                setFeedback('Pilih produk utama terlebih dahulu sebelum lanjut ke tahap berikutnya.', 'warning');
                return;
            }

            showStep(2);
            setFeedback('Step 2 aktif. Tambahkan add-on jika diperlukan, atau lanjut tanpa add-on.', 'info');
        });
        document.querySelectorAll('[data-step-prev]').forEach((button) => {
            button.addEventListener('click', () => {
                showStep(Number(button.dataset.stepPrev));
            });
        });
        document.querySelectorAll('[data-step-next]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!state.selectedProduct) {
                    showStep(1);
                    setFeedback('Pilih produk utama terlebih dahulu sebelum lanjut ke tahap berikutnya.', 'warning');
                    return;
                }

                showStep(Number(button.dataset.stepNext));
            });
        });
        stepIndicators.forEach((button) => {
            button.addEventListener('click', () => {
                const targetStep = Number(button.dataset.stepIndicator);

                if (targetStep === 5 && !state.lastBooking) {
                    setFeedback('Step konfirmasi akan aktif setelah booking berhasil dibuat.', 'warning');
                    return;
                }

                if (targetStep > 1 && !state.selectedProduct) {
                    showStep(1);
                    setFeedback('Mulai dari Step 1 dulu dengan memilih produk utama.', 'warning');
                    return;
                }

                showStep(targetStep);
            });
        });
        guestInput.addEventListener('input', () => {
            syncAddonEligibility();
            renderAddons();
            renderSummary();
            renderConfirmation();
            syncEstimate();
            checkAvailability();
        });
        checkoutDateInput.addEventListener('input', () => {
            renderSummary();
            renderConfirmation();
            syncEstimate();
            checkAvailability();
        });
        checkoutForm.addEventListener('submit', submitCheckout);
        document.getElementById('restart-booking').addEventListener('click', () => {
            state.currentStep = 1;
            state.selectedProduct = null;
            state.selectedAddons = [];
            state.customer = null;
            state.lastBooking = null;
            state.pricing = {
                subtotal: 0,
                addon_total: 0,
                fee_amount: 0,
                total_amount: 0,
                total_quantity: 0,
                loading: false,
            };
            checkoutForm.reset();
            checkoutDateInput.min = getToday();
            checkoutDateInput.value = prefilledVisitDate || getToday();
            guestInput.value = prefilledGuests || 2;
            tripDateInput.value = checkoutDateInput.value;
            tripGuestsInput.value = guestInput.value || prefilledGuests || 2;
            clearGatewayResult();
            renderMainProducts();
            syncAddonEligibility();
            renderAddons();
            renderSummary();
            renderConfirmation();
            syncPaymentHelper();
            updateStepIndicator();
            updateStepButtons();
            showStep(1);
        });

        checkoutDateInput.min = getToday();
        if (!checkoutDateInput.value) {
            checkoutDateInput.value = prefilledVisitDate || getToday();
        }
        if (!guestInput.value) {
            guestInput.value = prefilledGuests || 2;
        }
        tripDateInput.value = checkoutDateInput.value;
        tripGuestsInput.value = guestInput.value || prefilledGuests || 2;
        if (preselectedCategorySlug && preselectedCategorySlug !== 'all') {
            categoryFilter.value = preselectedCategorySlug;
        }

        renderMainProducts();
        syncAddonEligibility();
        renderAddons();
        renderSummary();
        renderConfirmation();
        syncPaymentHelper();
        updateStepIndicator();
        updateStepButtons();
        showStep(state.currentStep);
        if (state.selectedProduct) {
            setFeedback(`Produk ${state.selectedProduct.name} sudah dipilih dari halaman sebelumnya. Kamu bisa langsung lanjut atur add-on, atau kembali ke Step 1 untuk mengganti produk.`, 'info');
        }
        syncEstimate();
        checkAvailability();
    })();
</script>
@endsection
