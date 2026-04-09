<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>419 - Sesi Berakhir</title>
    <meta name="description" content="Sesi Anda telah berakhir. Muat ulang halaman untuk melanjutkan di CapolagaGo." />
    <link rel="icon" href="{{ asset('favicon.ico') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <main class="mx-auto flex min-h-screen max-w-3xl flex-col items-center justify-center px-6 text-center">
        <p class="mb-3 rounded-full border border-amber-400/30 bg-amber-400/10 px-4 py-1 text-sm text-amber-200">Error 419</p>
        <h1 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl">Sesi sudah berakhir</h1>
        <p class="mb-8 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">
            Permintaan tidak bisa diproses karena sesi halaman ini sudah kedaluwarsa. Coba muat ulang halaman, lalu lanjutkan kembali proses booking atau pembayaran Anda.
        </p>
        <div class="flex flex-col gap-3 sm:flex-row">
            <button type="button" onclick="window.location.reload()" class="rounded-lg bg-amber-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-300">
                Muat Ulang Halaman
            </button>
            <a href="{{ route('ticket.booking') }}" class="rounded-lg border border-white/15 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/5">
                Kembali ke Booking
            </a>
        </div>
    </main>
</body>
</html>
