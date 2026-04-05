@php
    $homeUrl = route('frontend.home');
    $bookingUrl = route('ticket.booking');
@endphp

<header class="fixed left-0 right-0 top-0 z-50 bg-white transition-all duration-300">
    <div class="container mx-auto px-4">
        <nav class="flex h-14 items-center justify-between md:h-16">
            <a class="flex items-center gap-2" href="{{ $homeUrl }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-tree-pine h-6 w-6 text-primary md:h-7 md:w-7" aria-hidden="true">
                    <path
                        d="m17 14 3 3.3a1 1 0 0 1-.7 1.7H4.7a1 1 0 0 1-.7-1.7L7 14h-.3a1 1 0 0 1-.7-1.7L9 9h-.2A1 1 0 0 1 8 7.3L12 3l4 4.3a1 1 0 0 1-.8 1.7H15l3 3.3a1 1 0 0 1-.7 1.7H17Z" />
                    <path d="M12 22v-3" />
                </svg>
                <span class="text-lg font-bold text-foreground md:text-xl">CapolagaGo</span>
            </a>

            <div class="hidden items-center gap-4 lg:flex">
                <a class="nav-link inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition-colors"
                    href="{{ $homeUrl }}" data-nav-target="home">
                    Home
                </a>
                <a class="nav-link inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition-colors" href="{{ $bookingUrl }}"
                    data-nav-target="booking">
                    Booking
                </a>
                <a class="nav-link inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition-colors" href="{{ $homeUrl }}#paket"
                    data-nav-target="paket">
                    Paket Wisata
                </a>
                <a class="nav-link inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition-colors" href="{{ $homeUrl }}#addon"
                    data-nav-target="addon">
                    Add-on Activity
                </a>
                <a class="nav-link inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition-colors" href="{{ $homeUrl }}#about"
                    data-nav-target="about">
                    Tentang Kami
                </a>
            </div>

            <div class="hidden items-center gap-3 lg:flex">
                <button class="flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                    type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-search h-4 w-4" aria-hidden="true">
                        <path d="m21 21-4.34-4.34" />
                        <circle cx="11" cy="11" r="8" />
                    </svg>
                    <span>Cari...</span>
                </button>
                <a data-slot="button"
                    class="inline-flex h-8 items-center justify-center gap-1.5 rounded-md bg-[#1a3a4a] px-3 text-sm font-medium text-white transition-all hover:bg-[#1a3a4a]/90"
                    href="{{ route('admin.dashboard') }}">
                    Login Admin
                </a>
            </div>

            <button class="p-2 lg:hidden" aria-label="Toggle menu" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-menu h-6 w-6 text-foreground" aria-hidden="true">
                    <path d="M4 5h16" />
                    <path d="M4 12h16" />
                    <path d="M4 19h16" />
                </svg>
            </button>
        </nav>
    </div>
</header>

<script>
    (() => {
        const homeUrl = @json($homeUrl);
        const bookingUrl = @json($bookingUrl);
        const links = document.querySelectorAll('[data-nav-target]');

        if (!links.length) {
            return;
        }

        const normalizePath = (url) => {
            const parsedUrl = new URL(url, window.location.origin);
            const trimmedPath = parsedUrl.pathname.replace(/\/+$/, '');

            return trimmedPath === '' ? '/' : trimmedPath;
        };

        const setLinkState = (activeTarget) => {
            links.forEach((link) => {
                const isActive = link.dataset.navTarget === activeTarget;

                link.classList.toggle('bg-teal-600', isActive);
                link.classList.toggle('text-white', isActive);
                link.classList.toggle('hover:bg-teal-700', isActive);
                link.classList.toggle('text-muted-foreground', !isActive);
                link.classList.toggle('hover:text-foreground', !isActive);
            });
        };

        const resolveActiveTarget = () => {
            const currentPath = normalizePath(window.location.href);
            const homePath = normalizePath(homeUrl);
            const bookingPath = normalizePath(bookingUrl);

            if (currentPath === bookingPath) {
                return 'booking';
            }

            if (currentPath !== homePath) {
                return null;
            }

            const hash = window.location.hash.replace('#', '');

            if (['paket', 'addon', 'about'].includes(hash)) {
                return hash;
            }

            return 'home';
        };

        const syncActiveMenu = () => {
            setLinkState(resolveActiveTarget());
        };  

        window.addEventListener('hashchange', syncActiveMenu);
        window.addEventListener('load', syncActiveMenu);
        syncActiveMenu();
    })();
</script>
