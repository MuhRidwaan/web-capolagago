@extends('frontend.layouts.main')

@section('title', 'Profile Customer - CapolagaGo')
@section('meta_description', 'Kelola profil customer CapolagaGo, ubah nama, nomor HP, email, dan password akun.')

@section('content')
@include('frontend.layouts.header')

<section class="pt-20 sm:pt-24 md:pt-28">
    <div class="border-b border-slate-200 bg-[linear-gradient(135deg,#153847_0%,#2a6e72_55%,#66a7a8_100%)]">
        <div class="mx-auto max-w-[1920px] px-4 py-12 md:px-8 md:py-16">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-white/70">Customer Area</p>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-white sm:text-4xl">Profile Saya</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
                    Kelola informasi akun Anda di sini, termasuk nama, nomor HP, email, dan password untuk keamanan akun.
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
                        <p class="mt-1 text-sm text-slate-500">{{ $user->phone ?: 'No. HP belum diisi' }}</p>
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
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
                    <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Total Ulasan</p>
                        <p class="mt-3 text-3xl font-bold text-slate-900">{{ $reviews->count() }}</p>
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
                            <label for="phone" class="mb-2 block text-sm font-semibold text-slate-700">No. WhatsApp</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" inputmode="tel" maxlength="14" placeholder="081234567890" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-100" />
                            @error('phone')
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

                <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="border-b border-slate-100 pb-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Ulasan Saya</p>
                        <h2 class="mt-2 text-2xl font-bold text-slate-900">Edit Ulasan</h2>
                        <p class="mt-2 text-sm text-slate-500">Kelola ulasan yang sudah pernah Anda kirim dari halaman profile.</p>
                    </div>

                    @if (session('success_review'))
                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {{ session('success_review') }}
                        </div>
                    @endif

                    @if ($reviews->isEmpty())
                        <div class="mt-6 rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center text-sm text-slate-500">
                            Belum ada ulasan yang bisa diedit. Setelah Anda mengirim ulasan dari detail booking, ulasannya akan tampil di sini.
                        </div>
                    @else
                        <div class="mt-6 space-y-5">
                            @foreach ($reviews as $review)
                                @php
                                    $activeReviewId = old('review_id');
                                    $isActiveReview = (string) $activeReviewId === (string) $review->id;
                                    $reviewRating = (int) ($isActiveReview ? old('rating', $review->rating) : $review->rating);
                                    $reviewComment = (string) ($isActiveReview ? old('comment', $review->comment) : ($review->comment ?? ''));
                                @endphp

                                <article class="rounded-[24px] border border-slate-200 p-5">
                                    <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-slate-900">{{ $review->product_name }}</h3>
                                            <p class="mt-1 text-sm text-slate-500">
                                                Booking {{ $review->booking_code }} • Kunjungan {{ \Illuminate\Support\Carbon::parse($review->visit_date)->translatedFormat('d M Y') }}
                                            </p>
                                        </div>
                                        <a href="{{ route('ticket.booking.status', ['token' => $review->public_token]) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-teal-300 hover:text-teal-700">
                                            Lihat Booking
                                        </a>
                                    </div>

                                    <form method="POST" action="{{ route('frontend.profile.reviews.update', ['review' => $review->id]) }}" class="mt-4 space-y-4">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="review_id" value="{{ $review->id }}">

                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">Rating</p>
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @for ($rating = 5; $rating >= 1; $rating--)
                                                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $reviewRating === $rating ? 'border-amber-300 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600 hover:border-teal-300 hover:text-teal-700' }}">
                                                        <input
                                                            type="radio"
                                                            name="rating"
                                                            value="{{ $rating }}"
                                                            class="sr-only"
                                                            {{ $reviewRating === $rating ? 'checked' : '' }}
                                                        >
                                                        {{ $rating }} ★
                                                    </label>
                                                @endfor
                                            </div>
                                            @if ($isActiveReview)
                                                @error('rating', 'updateReview')
                                                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                                @enderror
                                            @endif
                                        </div>

                                        <div>
                                            <label for="review-comment-{{ $review->id }}" class="mb-2 block text-sm font-semibold text-slate-900">Komentar</label>
                                            <textarea id="review-comment-{{ $review->id }}" name="comment" rows="4" maxlength="1000" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-100" placeholder="Tulis pengalaman Anda">{{ $reviewComment }}</textarea>
                                            @if ($isActiveReview)
                                                @error('comment', 'updateReview')
                                                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                                @enderror
                                            @endif
                                        </div>

                                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                            <p class="text-sm text-slate-500">
                                                Terakhir diperbarui {{ \Illuminate\Support\Carbon::parse($review->updated_at)->translatedFormat('d M Y H:i') }}
                                            </p>
                                            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700">
                                                Simpan Ulasan
                                            </button>
                                        </div>
                                    </form>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</section>
@endsection
