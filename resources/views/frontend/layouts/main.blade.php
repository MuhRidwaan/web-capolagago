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

        body {
            background-color: var(--bg);
            color: var(--fg);
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
    </style>
    @stack('head')
</head>
<body class="font-sans antialiased">
    <main class="min-h-screen">
        @yield('content')
        @include('frontend.layouts.footer')
    </main>
    @stack('scripts')
</body>
</html>
