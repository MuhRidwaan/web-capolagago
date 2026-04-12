<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'CapolagaGo - Experience Nature Adventure')</title>
    <meta name="description" content="@yield('meta_description', 'Platform wisata alam terintegrasi untuk booking camping, glamping, homestay, dan aktivitas outdoor di Capolaga.')" />
    <link rel="icon" href="{{ asset('favicon.ico') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --bg: #f8fafc;
            --fg: #0f172a;
            --muted: #64748b;
            --card: #ffffff;
            --border: #dbe4ee;
            --primary: #0ea5e9;
            --accent: #14b8a6;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            background-color: var(--bg);
            color: var(--fg);
            overflow-x: hidden;
        }

        .bg-background { background-color: var(--bg); }
        .text-foreground { color: var(--fg); }
        .text-muted-foreground { color: var(--muted); }
        .bg-card { background-color: var(--card); }
        .border-border { border-color: var(--border); }
        .text-primary { color: var(--primary); }
        .bg-primary { background-color: var(--primary); }
        .bg-accent { background-color: var(--accent); }
        .shadow-xs { box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06); }

        img,
        svg,
        video,
        canvas,
        iframe {
            max-width: 100%;
        }

        input,
        select,
        textarea,
        button {
            max-width: 100%;
        }

        @media (max-width: 767px) {
            [class*="grid-cols-["] {
                grid-template-columns: minmax(0, 1fr) !important;
            }

            [class*="max-w-["],
            [class*="min-w-["] {
                min-width: 0;
            }

            [class*="px-8"] {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            [class*="md:px-8"],
            [class*="lg:px-8"],
            [class*="xl:px-8"] {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
        }
    </style>
    @stack('head')
</head>
<body class="font-sans antialiased">
    <main class="min-h-screen overflow-x-clip">
        @yield('content')
        @include('frontend.layouts.footer')
    </main>
    @stack('scripts')
</body>
</html>
