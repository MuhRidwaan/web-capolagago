<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>503 - Layanan Sementara Tidak Tersedia</title>
    <meta name="description" content="CapolagaGo sedang dalam pemeliharaan atau layanan sementara tidak tersedia." />
    <link rel="icon" href="{{ asset('favicon.ico') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <main class="mx-auto flex min-h-screen max-w-3xl flex-col items-center justify-center px-6 text-center">
        <p class="mb-3 rounded-full border border-sky-400/30 bg-sky-400/10 px-4 py-1 text-sm text-sky-200">Error 503</p>
        <h1 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl">Layanan sedang tidak tersedia</h1>
        <p class="mb-8 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">
            CapolagaGo sedang menjalani pemeliharaan atau trafik sedang tinggi. Silakan kembali beberapa saat lagi untuk melanjutkan pemesanan atau melihat paket wisata.
        </p>
        <div class="flex flex-col gap-3 sm:flex-row">
            <button type="button" onclick="window.location.reload()" class="rounded-lg bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-sky-300">
                Periksa Lagi
            </button>
            <a href="{{ route('frontend.wisata') }}" class="rounded-lg border border-white/15 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/5">
                Lihat Paket Wisata
            </a>
        </div>
    </main>
</body>
</html>
