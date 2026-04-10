@extends('frontend.layouts.main')

@section('title', 'Profile Customer - CapolagaGo')
@section('meta_description', 'Kelola profil customer CapolagaGo, ubah nama, email, dan password akun.')

@section('content')
@include('frontend.layouts.header')

<section class="pt-20 sm:pt-24 md:pt-28">
    <div class="border-b border-slate-200 bg-[linear-gradient(135deg,#153847_0%,#2a6e72_55%,#66a7a8_100%)]">
        <div class="mx-auto max-w-[1920px] px-4 py-12 md:px-8 md:py-16">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-white/70">Customer Area</p>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-white sm:text-4xl">Profile Saya</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
                    Kelola informasi akun Anda di sini, termasuk nama, email, dan password untuk keamanan akun.
                </p>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-[1920px] px-4 py-8 md:px-8 md:py-10">
        <div class="grid gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
            <aside class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-teal-100 text-xl font-bold text-teal-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                        <p class="text-sm text-slate-500">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4">
                    <div class="rounded-[24px] bg-slate-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Role</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $user->getRoleNames()->join(', ') ?: 'Customer' }}</p>
                    </div>
                    <div class="rounded-[24px] bg-slate-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Bergabung Sejak</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $user->created_at?->translatedFormat('d M Y') }}</p>
                    </div>
                    <div class="rounded-[24px] bg-slate-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Total Booking</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $bookingSummary->count() }} pesanan</p>
                    </div>
                </div>

                <a href="{{ route('frontend.orders') }}" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-teal-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-teal-700">
                    Lihat Pesanan
                </a>
            </aside>

            <div class="space-y-6">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Total Paket Dipesan</p>
                        <p class="mt-3 text-3xl font-bold text-slate-900">{{ $bookingSummary->count() }}</p>
                    </div>
                    <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Menunggu Pembayaran</p>
                        <p class="mt-3 text-3xl font-bold text-amber-600">{{ $bookingSummary->whereIn('status', ['pending', 'waiting_payment'])->count() }}</p>
                    </div>
                    <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Selesai / Aktif</p>
                        <p class="mt-3 text-3xl font-bold text-emerald-600">{{ $bookingSummary->whereIn('status', ['confirmed', 'checked_in', 'completed'])->count() }}</p>
                    </div>
                </div>

                <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="border-b border-slate-100 pb-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Informasi Akun</p>
                        <h2 class="mt-2 text-2xl font-bold text-slate-900">Edit Profile</h2>
                    </div>

                    @if (session('success_profile'))
                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {{ session('success_profile') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('frontend.profile.update') }}" class="mt-6 grid gap-5">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Nama Lengkap</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-100" />
                            @error('name')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                            <div class="flex h-12 w-full items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-500">
                                {{ $user->email }}
                            </div>
                            <!-- <p class="mt-2 text-xs text-slate-400">Email tidak dapat diubah dari halaman profile.</p> -->
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                                Simpan Profile
                            </button>
                        </div>
                    </form>
                </section>

                <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="border-b border-slate-100 pb-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Keamanan Akun</p>
                        <h2 class="mt-2 text-2xl font-bold text-slate-900">Ubah Password</h2>
                    </div>

                    @if (session('success_password'))
                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {{ session('success_password') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('frontend.profile.password.update') }}" class="mt-6 grid gap-5">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="current_password" class="mb-2 block text-sm font-semibold text-slate-700">Password Saat Ini</label>
                            <input id="current_password" name="current_password" type="password" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-100" />
                            @error('current_password')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password Baru</label>
                            <input id="password" name="password" type="password" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-100" />
                            @error('password')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Konfirmasi Password Baru</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-100" />
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700">
                                Ubah Password
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</section>
@endsection
