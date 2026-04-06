@php
    $homeUrl = route('frontend.home');
    $wisataUrl = route('frontend.wisata');
    $bookingUrl = route('ticket.booking');
    $adminUrl = route('admin.dashboard');
    $isHome = request()->routeIs('frontend.home');
    $isWisata = request()->routeIs('frontend.wisata');
    $isBooking = request()->routeIs('ticket.booking');
    $currentUser = auth()->user();

    $navClass = static function (bool $active): string {
        return $active
            ? 'inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white'
            : 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900';
    };
@endphp

<header class="fixed left-0 right-0 top-0 z-50 border-b border-slate-200/70 bg-white/95 backdrop-blur-sm transition-all duration-300">
    <div class="mx-auto max-w-[1920px] px-4 md:px-8">
        <nav class="flex h-14 items-center justify-between md:h-16">
            <a class="flex items-center gap-2" href="{{ $homeUrl }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-sky-500 md:h-7 md:w-7" aria-hidden="true">
                    <path d="m17 14 3 3.3a1 1 0 0 1-.7 1.7H4.7a1 1 0 0 1-.7-1.7L7 14h-.3a1 1 0 0 1-.7-1.7L9 9h-.2A1 1 0 0 1 8 7.3L12 3l4 4.3a1 1 0 0 1-.8 1.7H15l3 3.3a1 1 0 0 1-.7 1.7H17Z"></path>
                    <path d="M12 22v-3"></path>
                </svg>
                <span class="text-lg font-bold text-slate-900 md:text-xl">CapolagaGo</span>
            </a>

            <div class="hidden items-center gap-2 lg:flex">
                <a class="{{ $navClass($isHome) }}" href="{{ $homeUrl }}">Home</a>
                <a class="{{ $navClass($isBooking) }}" href="{{ $bookingUrl }}">Booking</a>
                <a class="{{ $navClass($isWisata) }}" href="{{ $wisataUrl }}">Paket Wisata</a>
                <a class="{{ $navClass(false) }}" href="{{ $homeUrl }}#addon">Add-on Activity</a>
                <a class="{{ $navClass(false) }}" href="{{ $homeUrl }}#about">Tentang Kami</a>
            </div>

            <div class="hidden items-center gap-3 lg:flex">
                <form action="{{ $wisataUrl }}" method="GET" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-500 transition-colors hover:text-slate-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                        <path d="m21 21-4.34-4.34"></path>
                        <circle cx="11" cy="11" r="8"></circle>
                    </svg>
                    <input name="q" type="search" value="{{ request('q', '') }}" placeholder="Cari..." class="w-24 bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400" />
                </form>
                @if ($currentUser)
                    <a class="inline-flex items-center justify-center rounded-md bg-[#1a3a4a] px-3 py-2 text-sm font-medium text-white transition hover:bg-[#1a3a4a]/90" href="{{ $adminUrl }}">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-md border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            Logout
                        </button>
                    </form>
                @else
                    <a class="inline-flex items-center justify-center rounded-md bg-[#1a3a4a] px-3 py-2 text-sm font-medium text-white transition hover:bg-[#1a3a4a]/90" href="{{ route('login') }}">Login</a>
                @endif
            </div>

            <details class="relative lg:hidden">
                <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-900 transition hover:bg-slate-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6" aria-hidden="true">
                        <path d="M4 5h16"></path>
                        <path d="M4 12h16"></path>
                        <path d="M4 19h16"></path>
                    </svg>
                </summary>

                <div class="absolute right-0 top-full mt-3 w-[min(20rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl">
                    <div class="space-y-2">
                        <a class="{{ $navClass($isHome) }} w-full justify-start" href="{{ $homeUrl }}">Home</a>
                        <a class="{{ $navClass($isBooking) }} w-full justify-start" href="{{ $bookingUrl }}">Booking</a>
                        <a class="{{ $navClass($isWisata) }} w-full justify-start" href="{{ $wisataUrl }}">Paket Wisata</a>
                        <a class="{{ $navClass(false) }} w-full justify-start" href="{{ $homeUrl }}#addon">Add-on Activity</a>
                        <a class="{{ $navClass(false) }} w-full justify-start" href="{{ $homeUrl }}#about">Tentang Kami</a>
                    </div>

                    <form action="{{ $wisataUrl }}" method="GET" class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <label class="block">
                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Cari</span>
                            <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0" aria-hidden="true">
                                    <path d="m21 21-4.34-4.34"></path>
                                    <circle cx="11" cy="11" r="8"></circle>
                                </svg>
                                <input name="q" type="search" value="{{ request('q', '') }}" placeholder="Cari..." class="w-full bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400" />
                            </div>
                        </label>
                    </form>

                    @if ($currentUser)
                        <a class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-[#1a3a4a] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#1a3a4a]/90" href="{{ $adminUrl }}">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="mt-3">
                            @csrf
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Logout
                            </button>
                        </form>
                    @else
                        <a class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-[#1a3a4a] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#1a3a4a]/90" href="{{ route('login') }}">Login</a>
                    @endif
                </div>
            </details>
        </nav>
    </div>
</header>
