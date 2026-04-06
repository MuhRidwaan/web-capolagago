@php
    $homeUrl = route('frontend.home');
    $wisataUrl = route('frontend.wisata');
    $bookingUrl = route('ticket.booking');
    $ctaBackgroundUrl = asset('images/glamping.jpg');
@endphp

<section id="about" class="scroll-mt-24 bg-[#264f63] text-white">
    <div class="relative overflow-hidden border-t border-white/10">
        <div class="absolute inset-0">
            <img src="{{ $ctaBackgroundUrl }}" alt="" class="h-full w-full object-cover object-center opacity-45" />
            <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(14,43,56,0.9)_0%,rgba(16,53,70,0.82)_42%,rgba(21,67,86,0.86)_100%)]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.12),transparent_35%)]"></div>
        </div>

        <div class="relative mx-auto max-w-[1440px] px-4 py-10 md:px-8 md:py-14">
            <div class="grid gap-8 lg:gap-10 xl:grid-cols-[minmax(0,38rem)_minmax(320px,30rem)] xl:items-center xl:justify-between">
                <div class="max-w-[38rem]">
                    <h2 class="max-w-sm text-3xl font-bold leading-[1.05] tracking-tight text-white sm:text-4xl">
                        Rencanakan<br />
                        Petualanganmu<br />
                        Sekarang
                    </h2>
                    <p class="mt-5 max-w-lg text-sm leading-7 text-white/80">
                        Jangan lewatkan pengalaman wisata alam terbaik di Capolaga. Booking sekarang dan dapatkan penawaran menarik.
                    </p>

                    <ul class="mt-6 space-y-3 text-sm text-white/85">
                        <li class="flex items-center gap-3">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-cyan-300/40 bg-cyan-400/10 text-cyan-300">&#10003;</span>
                            <span>Booking instan &amp; konfirmasi cepat</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-cyan-300/40 bg-cyan-400/10 text-cyan-300">&#10003;</span>
                            <span>Harga transparan tanpa biaya tersembunyi</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-cyan-300/40 bg-cyan-400/10 text-cyan-300">&#10003;</span>
                            <span>Customer support 24/7</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-cyan-300/40 bg-cyan-400/10 text-cyan-300">&#10003;</span>
                            <span>Pembatalan gratis hingga 24 jam sebelumnya</span>
                        </li>
                    </ul>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a href="{{ $bookingUrl }}" class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-[#31b3c1] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#28a4b2] sm:w-auto">
                            Booking Sekarang
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </a>
                        <a href="{{ $wisataUrl }}" class="inline-flex w-full items-center justify-center rounded-md border border-white/25 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10 sm:w-auto">
                            Lihat Paket Wisata
                        </a>
                    </div>
                </div>

                <div class="w-full rounded-[26px] border border-white/15 bg-white/10 p-5 shadow-2xl backdrop-blur-sm sm:p-6 xl:max-w-[30rem] xl:justify-self-end xl:p-7">
                    <h3 class="text-lg font-bold text-white xl:text-[2rem] xl:leading-none">Butuh Bantuan?</h3>
                    <p class="mt-3 text-sm leading-6 text-white/75">
                        Tim kami siap membantu Anda merencanakan perjalanan wisata yang sempurna.
                    </p>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-4 sm:gap-4">
                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#31b3c1] text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs text-white/65">Telepon</p>
                                <a href="tel:+6281234567890" class="mt-1 block text-sm font-medium text-white/90 transition hover:text-white">+62 812-3456-7890</a>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-4 sm:gap-4">
                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#31b3c1] text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
                                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                    <path d="m22 7-8.97 5.7a2 2 0 0 1-2.06 0L2 7"></path>
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs text-white/65">Email</p>
                                <a href="mailto:info@capolagago.com" class="mt-1 block text-sm font-medium text-white/90 transition hover:text-white">info@capolagago.com</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="border-t border-white/10 bg-[#1e475b]">
        <div class="mx-auto max-w-[1440px] px-4 py-10 md:px-8 md:py-12">
            <div class="grid gap-10 md:grid-cols-2 xl:grid-cols-[1.2fr_0.8fr_1fr_1fr]">
                <div>
                    <a href="{{ $homeUrl }}" class="inline-flex items-center gap-2 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-[#35c3d1]" aria-hidden="true">
                            <path d="m17 14 3 3.3a1 1 0 0 1-.7 1.7H4.7a1 1 0 0 1-.7-1.7L7 14h-.3a1 1 0 0 1-.7-1.7L9 9h-.2A1 1 0 0 1 8 7.3L12 3l4 4.3a1 1 0 0 1-.8 1.7H15l3 3.3a1 1 0 0 1-.7 1.7H17Z"></path>
                            <path d="M12 22v-3"></path>
                        </svg>
                        <span class="text-xl font-bold">CapolagaGo</span>
                    </a>
                    <p class="mt-5 max-w-xs text-sm leading-7 text-white/65">
                        Platform wisata alam terintegrasi untuk pengalaman petualangan terbaik di Capolaga, Subang.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase tracking-[0.18em] text-white">Navigasi</h3>
                    <ul class="mt-5 space-y-3 text-sm text-white/70">
                        <li><a href="{{ $homeUrl }}" class="transition hover:text-white">Home</a></li>
                        <li><a href="{{ $bookingUrl }}" class="transition hover:text-white">Booking</a></li>
                        <li><a href="{{ $wisataUrl }}" class="transition hover:text-white">Paket Wisata</a></li>
                        <li><a href="{{ $homeUrl }}#addon" class="transition hover:text-white">Add-on Activity</a></li>
                        <li><a href="{{ $homeUrl }}#about" class="transition hover:text-white">Tentang Kami</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase tracking-[0.18em] text-white">Layanan</h3>
                    <ul class="mt-5 space-y-3 text-sm text-white/70">
                        <li><a href="{{ $wisataUrl }}?category=glamping" class="transition hover:text-white">Glamping</a></li>
                        <li><a href="{{ $wisataUrl }}?category=camping" class="transition hover:text-white">Camping</a></li>
                        <li><a href="{{ $wisataUrl }}?category=homestay" class="transition hover:text-white">Homestay</a></li>
                        <li><span class="cursor-not-allowed text-white/40" title="Add-on dipilih setelah produk utama">Rafting</span></li>
                        <li><span class="cursor-not-allowed text-white/40" title="Add-on dipilih setelah produk utama">ATV Adventure</span></li>
                        <li><span class="cursor-not-allowed text-white/40" title="Add-on dipilih setelah produk utama">Paragliding</span></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase tracking-[0.18em] text-white">Kontak</h3>
                    <ul class="mt-5 space-y-4 text-sm text-white/70">
                        <li>Capolaga, Subang, Jawa Barat</li>
                        <li><a href="tel:+6281234567890" class="transition hover:text-white">+62 812-3456-7890</a></li>
                        <li><a href="mailto:info@capolagago.com" class="transition hover:text-white">info@capolagago.com</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-white/10 pt-5 text-xs text-white/50 md:flex-row md:items-center md:justify-between">
                <p>&copy; 2026 CapolagaGo. All rights reserved.</p>
                <div class="flex flex-wrap items-center gap-4 md:gap-5">
                    <a href="#" class="transition hover:text-white/80">Privacy Policy</a>
                    <a href="#" class="transition hover:text-white/80">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</section>
