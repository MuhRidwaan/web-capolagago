<!DOCTYPE html><html lang="{{ str_replace('_', '-', app()->getLocale()) }}"><head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>CapolagaGo - Experience Nature Adventure</title>
  <meta name="description" content="Platform wisata alam terintegrasi untuk booking camping, glamping, homestay & aktivitas outdoor di Capolaga. Temukan petualangan alam terbaik!" />
  <link rel="icon" href="{{ asset('favicon.ico') }}" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
:root { --bg: #f8fafc; --fg: #0f172a; --muted: #64748b; --card: #ffffff; --border: #e2e8f0; --primary: #0ea5e9; --primary-foreground: #ffffff; --accent: #14b8a6; --accent-foreground: #ffffff; }
.bg-background { background-color: var(--bg); }
.text-foreground { color: var(--fg); }
.text-muted-foreground { color: var(--muted); }
.bg-card { background-color: var(--card); }
.border-border { border-color: var(--border); }
.text-primary { color: var(--primary); }
.bg-primary { background-color: var(--primary); }
.text-primary-foreground { color: var(--primary-foreground); }
.bg-accent { background-color: var(--accent); }
.text-accent-foreground { color: var(--accent-foreground); }
.border-divider { border-color: #d1d5db; }
.bg-muted { background-color: #f1f5f9; }
.border-input { border-color: #cbd5e1; }
.bg-input { background-color: #f8fafc; }
.text-card-foreground { color: #0f172a; }
.shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
</style>
</head><body class="font-sans antialiased"><div hidden=""><!--$--><!--/$--></div><main class="min-h-screen">
    @yield('content')
    @include('frontend.layouts.footer')
</main><!--$--><!--/$--><!--$!--><!--/$-->
</body></html>
