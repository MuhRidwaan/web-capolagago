<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>404 - Halaman Tidak Ditemukan</title>
    <meta name="description" content="Halaman yang Anda cari tidak tersedia di CapolagaGo." />
    <link rel="icon" href="{{ asset('favicon.ico') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <main class="mx-auto flex min-h-screen max-w-3xl flex-col items-center justify-center px-6 text-center">
        <p class="mb-3 rounded-full border border-white/15 bg-white/5 px-4 py-1 text-sm text-slate-300">Error 404</p>
        <h1 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl">Halaman tidak ditemukan</h1>
        <p class="mb-8 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">
            Link yang Anda buka mungkin sudah dipindahkan atau tidak tersedia lagi. Silakan kembali ke beranda untuk melanjutkan eksplorasi wisata Capolaga.
        </p>
        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('frontend.home') }}" class="rounded-lg bg-teal-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-teal-400">
                Kembali ke Beranda
            </a>
            <a href="{{ route('frontend.wisata') }}" class="rounded-lg border border-white/15 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/5">
                Lihat Paket Wisata
            </a>
        </div>
    </main>
</body>
</html>
