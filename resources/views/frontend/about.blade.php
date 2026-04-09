@extends('frontend.layouts.main')

@section('title', 'Tentang CapolagaGo')
@section('meta_description', 'Kenal lebih dekat dengan CapolagaGo, platform booking wisata alam Capolaga untuk pengalaman camping, glamping, homestay, dan aktivitas outdoor.')

@section('content')
@include('frontend.layouts.header')

<section class="relative overflow-hidden border-b border-slate-200 pt-20 md:pt-24">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(45,157,168,0.16),transparent_36%),linear-gradient(135deg,#f8fafc_0%,#eef6f7_48%,#f7fbfc_100%)]"></div>
    <div class="absolute -right-20 top-20 h-64 w-64 rounded-full bg-teal-200/30 blur-3xl"></div>
    <div class="absolute -left-16 bottom-10 h-72 w-72 rounded-full bg-sky-200/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-[1200px] px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)] lg:items-start">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-teal-700">Tentang CapolagaGo</p>
                <h1 class="mt-4 max-w-3xl text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">
                    Gerbang digital untuk pengalaman alam Capolaga yang lebih rapi, nyaman, dan mudah dipesan.
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600">
                    CapolagaGo hadir untuk membantu wisatawan menemukan paket wisata alam terbaik di Capolaga, mulai dari glamping, camping, homestay, hingga aktivitas pendamping. Kami merapikan proses pencarian, booking, dan pembayaran agar tamu bisa fokus menikmati pengalaman di alam terbuka.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <article class="rounded-[28px] border border-white/70 bg-white/80 p-5 shadow-sm backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Fokus Kami</p>
                    <p class="mt-3 text-2xl font-bold text-slate-900">Wisata alam yang mudah diakses</p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Dari pencarian paket sampai pembayaran, semuanya dirancang lebih jelas untuk keluarga, rombongan, dan pecinta aktivitas outdoor.
                    </p>
                </article>
                <article class="rounded-[28px] border border-teal-100 bg-teal-50 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-teal-700">Nilai Utama</p>
                    <p class="mt-3 text-2xl font-bold text-slate-900">Transparan, hangat, dan siap membantu</p>
                    <p class="mt-3 text-sm leading-7 text-slate-700">
                        Informasi paket, biaya tambahan, serta alur pemesanan dibuat seterang mungkin agar keputusan tamu terasa lebih yakin.
                    </p>
                </article>
            </div>
        </div>
    </div>
</section>

@endsection
