<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>500 - Terjadi Kendala</title>
    <meta name="description" content="Terjadi kendala internal di CapolagaGo. Silakan coba lagi beberapa saat lagi." />
    <link rel="icon" href="{{ asset('favicon.ico') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <main class="mx-auto flex min-h-screen max-w-3xl flex-col items-center justify-center px-6 text-center">
        <p class="mb-3 rounded-full border border-rose-400/30 bg-rose-400/10 px-4 py-1 text-sm text-rose-200">Error 500</p>
        <h1 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl">Terjadi kendala di sistem</h1>
        <p class="mb-8 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">
            Maaf, halaman ini sedang mengalami gangguan internal. Tim kami perlu beberapa saat untuk memulihkannya. Silakan coba lagi sebentar lagi atau kembali ke beranda.
        </p>
        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('frontend.home') }}" class="rounded-lg bg-teal-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-teal-400">
                Kembali ke Beranda
            </a>
            <button type="button" onclick="window.location.reload()" class="rounded-lg border border-white/15 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/5">
                Coba Lagi
            </button>
        </div>
    </main>
</body>
</html>
