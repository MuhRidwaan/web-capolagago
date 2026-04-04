@extends('frontend.layouts.main')

@section('content')
<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-white border-b border-slate-200">
      <div class="container mx-auto px-4">
        <nav class="flex items-center justify-between h-14 md:h-16">
          <a class="flex items-center gap-2" href="{{ route('frontend.home') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 md:h-7 md:w-7 text-sky-500" aria-hidden="true"><path d="m17 14 3 3.3a1 1 0 0 1-.7 1.7H4.7a1 1 0 0 1-.7-1.7L7 14h-.3a1 1 0 0 1-.7-1.7L9 9h-.2A1 1 0 0 1 8 7.3L12 3l4 4.3a1 1 0 0 1-.8 1.7H15l3 3.3a1 1 0 0 1-.7 1.7H17Z"></path><path d="M12 22v-3"></path></svg>
            <span class="text-lg md:text-xl font-bold text-slate-900">CapolagaGo</span>
          </a>
          <div class="hidden lg:flex items-center gap-4">
            <a class="text-sm font-medium transition-colors text-slate-500 hover:text-slate-900" href="{{ route('frontend.home') }}">Home</a>
            <a class="inline-flex items-center px-4 py-2 rounded-lg bg-teal-600 text-white text-sm font-semibold hover:bg-teal-700 transition-colors" href="{{ route('ticket.booking') }}">Booking</a>
            <a class="text-sm font-medium transition-colors text-slate-500 hover:text-slate-900" href="{{ route('frontend.home') }}#paket">Paket Wisata</a>
            <a class="text-sm font-medium transition-colors text-slate-500 hover:text-slate-900" href="{{ route('frontend.home') }}#addon">Add-on Activity</a>
            <a class="text-sm font-medium transition-colors text-slate-500 hover:text-slate-900" href="{{ route('frontend.home') }}#about">Tentang Kami</a>
          </div>
          <div class="hidden lg:flex items-center gap-3">
            <button class="flex items-center gap-2 px-3 py-2 text-sm text-slate-500 hover:text-slate-900 transition-colors" type="button">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="m21 21-4.34-4.34"></path><circle cx="11" cy="11" r="8"></circle></svg>
              <span>Cari...</span>
            </button>
            <a class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium transition-all h-8 rounded-md gap-1.5 px-3 bg-[#1a3a4a] hover:bg-[#1a3a4a]/90 text-white" href="{{ route('admin.dashboard') }}">Login Admin di sini dulu ya</a>
          </div>
          <button class="lg:hidden p-2" aria-label="Toggle menu" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-slate-900" aria-hidden="true"><path d="M4 5h16"></path><path d="M4 12h16"></path><path d="M4 19h16"></path></svg>
          </button>
        </nav>
      </div>
    </header>

    <section class="border-b border-slate-200 bg-white pt-20 md:pt-24">
      <div class="mx-auto max-w-[1720px] px-4 py-5 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <a href="{{ route('frontend.home') }}" class="mb-3 inline-flex items-center gap-2 text-sm text-slate-500 transition hover:text-slate-900">
              <span aria-hidden="true">←</span>
              <span>Kembali ke beranda</span>
            </a>

            <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Booking yang sederhana dan jelas</h1>
            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600 sm:text-base">
              Pilih akomodasi, tambah aktivitas bila perlu, lalu lanjutkan checkout tanpa tampilan yang terlalu ramai.
            </p>
        </div>
      </div>
    </section>

    <section class="pb-12 pt-6">
      <div class="mx-auto grid max-w-[1720px] gap-6 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:px-8">
        <div class="space-y-6">
          <section class="rounded-2xl border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap gap-2">
              <button data-step="1" class="step-chip rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-900">1. Pilih Produk</button>
              <button data-step="2" class="step-chip rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-500">2. Add-on</button>
              <button data-step="3" class="step-chip rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-500">3. Keranjang</button>
              <button data-step="4" class="step-chip rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-500">4. Checkout</button>
              <button data-step="5" class="step-chip rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-500">5. Konfirmasi</button>
            </div>
            <div id="flow-feedback" class="mt-3 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"></div>
          </section>

          <section data-panel="1" class="booking-panel rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Step 1</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">Pilih produk utama</h2>
                <p class="mt-1 text-sm text-slate-600">Tentukan akomodasi yang paling cocok sebelum melanjutkan ke add-on activity.</p>
              </div>
              <label class="block">
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Filter</span>
                <select id="category-filter" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:bg-white">
                  <option value="all">Semua kategori</option>
                  <option value="glamping">Glamping</option>
                  <option value="camping">Camping</option>
                  <option value="homestay">Homestay</option>
                </select>
              </label>
            </div>

            <div id="product-grid" class="grid gap-4 xl:grid-cols-2"></div>

            <div class="mt-6 flex justify-end">
              <button id="to-step-2" class="rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                Lanjut ke Add-on
              </button>
            </div>
          </section>

          <section data-panel="2" class="booking-panel hidden rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="mb-5 flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Step 2</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">Tambahkan pengalaman pelengkap</h2>
                <p class="mt-1 text-sm text-slate-600">Add-on membantu menaikkan nilai transaksi sekaligus membuat paket wisata lebih menarik.</p>
              </div>
              <button id="back-to-1" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                Kembali
              </button>
            </div>

            <div id="addon-grid" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"></div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-between">
              <p class="text-sm text-slate-500">Tip: pilih add-on yang benar-benar relevan supaya checkout tetap terasa ringan.</p>
              <button id="to-step-3" class="rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                Review Keranjang
              </button>
            </div>
          </section>

          <section data-panel="3" class="booking-panel hidden rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="mb-5 flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Step 3</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">Periksa keranjang</h2>
                <p class="mt-1 text-sm text-slate-600">Pastikan paket utama, add-on, dan jumlah peserta sudah sesuai sebelum checkout.</p>
              </div>
              <button id="back-to-2" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                Kembali
              </button>
            </div>

            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_280px]">
              <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Produk Dipilih</p>
                  <div id="cart-main" class="mt-3"></div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <div class="flex items-center justify-between gap-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Add-on Activity</p>
                    <span id="addon-count" class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600">0 dipilih</span>
                  </div>
                  <div id="cart-addons" class="mt-3 space-y-3"></div>
                </div>
              </div>

              <aside class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Estimasi Harga</p>
                <div class="mt-4 space-y-3 text-sm text-slate-700">
                  <div class="flex items-center justify-between">
                    <span>Paket utama</span>
                    <span id="summary-base">Rp 0</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span>Add-on</span>
                    <span id="summary-addon">Rp 0</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span>Service fee</span>
                    <span id="summary-fee">Rp 0</span>
                  </div>
                  <div class="border-t border-slate-200 pt-3">
                    <div class="flex items-center justify-between text-base font-bold text-slate-900">
                      <span>Total</span>
                      <span id="summary-total">Rp 0</span>
                    </div>
                  </div>
                </div>

                <button id="to-step-4" class="mt-5 w-full rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                  Lanjut ke Checkout
                </button>
              </aside>
            </div>
          </section>

          <section data-panel="4" class="booking-panel hidden rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="mb-5 flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Step 4</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">Checkout customer</h2>
                <p class="mt-1 text-sm text-slate-600">Isi data inti yang diperlukan agar tim operasional bisa memproses booking lebih cepat.</p>
              </div>
              <button id="back-to-3" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                Kembali
              </button>
            </div>

            <form id="checkout-form" class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
              <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                  <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Nama lengkap</span>
                    <input name="name" type="text" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400" placeholder="Nama customer" />
                  </label>
                  <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-slate-700">No. WhatsApp</span>
                    <input name="phone" type="tel" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400" placeholder="08xxxxxxxxxx" />
                  </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                  <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Email</span>
                    <input name="email" type="email" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400" placeholder="email@contoh.com" />
                  </label>
                  <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Tanggal kunjungan</span>
                    <input id="checkout-date-input" name="date" type="date" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400" />
                  </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                  <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Jumlah peserta</span>
                    <input id="guest-input" name="guests" type="number" min="1" value="2" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400" />
                  </label>
                  <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Metode pembayaran</span>
                    <select name="payment" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400">
                      <option value="Transfer Bank">Transfer Bank</option>
                      <option value="QRIS">QRIS</option>
                      <option value="E-Wallet">E-Wallet</option>
                    </select>
                  </label>
                </div>

                <label class="block">
                  <span class="mb-2 block text-sm font-semibold text-slate-700">Catatan tambahan</span>
                  <textarea name="notes" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-emerald-400" placeholder="Contoh: late check-in, butuh area dekat sungai, dsb."></textarea>
                </label>
              </div>

              <aside class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Ringkasan Pembayaran</p>
                <div class="mt-4 space-y-3 text-sm">
                  <div class="flex items-center justify-between">
                    <span class="text-slate-600">Paket</span>
                    <span id="checkout-base">Rp 0</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-slate-600">Add-on</span>
                    <span id="checkout-addon">Rp 0</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-slate-600">Service fee</span>
                    <span id="checkout-fee">Rp 0</span>
                  </div>
                  <div class="border-t border-slate-200 pt-3">
                    <div class="flex items-center justify-between text-base font-bold">
                      <span>Total bayar</span>
                      <span id="checkout-total">Rp 0</span>
                    </div>
                  </div>
                </div>

                <button type="submit" class="mt-5 w-full rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                  Bayar dan Konfirmasi
                </button>
              </aside>
            </form>
          </section>

          <section data-panel="5" class="booking-panel hidden rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="mx-auto max-w-2xl text-center">
              <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-3xl text-emerald-700">✓</div>
              <p class="mt-4 text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Step 5</p>
              <h2 class="mt-2 text-3xl font-bold text-slate-900">Booking berhasil dibuat</h2>
              <p class="mt-3 text-sm leading-6 text-slate-600">
                Flow booking sudah lebih rapi: customer selesai memilih produk, add-on, checkout, dan langsung mendapat ringkasan konfirmasi.
              </p>

              <div class="mt-6 grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-left sm:grid-cols-2">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Produk</p>
                  <p id="confirmation-product" class="mt-2 text-sm font-semibold text-slate-900">-</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Total</p>
                  <p id="confirmation-total" class="mt-2 text-sm font-semibold text-slate-900">Rp 0</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Atas nama</p>
                  <p id="confirmation-name" class="mt-2 text-sm font-semibold text-slate-900">-</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Kontak</p>
                  <p id="confirmation-contact" class="mt-2 text-sm font-semibold text-slate-900">-</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Tanggal kunjungan</p>
                  <p id="confirmation-date" class="mt-2 text-sm font-semibold text-slate-900">-</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Jumlah peserta</p>
                  <p id="confirmation-guests" class="mt-2 text-sm font-semibold text-slate-900">-</p>
                </div>
              </div>

              <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('ticket.booking') }}" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                  Lihat Paket Lain
                </a>
                <button id="restart-booking" class="rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                  Buat Booking Baru
                </button>
              </div>
            </div>
          </section>
        </div>

        <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-4 lg:sticky lg:top-6">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Ringkasan Live</p>
              <h2 class="mt-1 text-base font-bold text-slate-900">Booking customer</h2>
            </div>
            <span id="sidebar-addon-count" class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">0 add-on</span>
          </div>

          <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Kunjungan (Read-only)</p>
            <div class="mt-3 grid gap-2">
              <input id="trip-date-input" type="date" readonly class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm text-slate-700 outline-none" />
              <div class="flex items-center rounded-xl border border-slate-200 bg-slate-100">
                <button type="button" data-adjust-guests="-1" disabled class="inline-flex h-10 w-10 items-center justify-center text-lg font-semibold text-slate-400">-</button>
                <input id="trip-guests-input" type="number" min="1" value="2" readonly class="h-10 w-full border-x border-slate-200 bg-slate-100 px-3 text-center text-sm font-semibold text-slate-700 outline-none" />
                <button type="button" data-adjust-guests="1" disabled class="inline-flex h-10 w-10 items-center justify-center text-lg font-semibold text-slate-400">+</button>
              </div>
              <div class="flex items-center justify-between gap-2 text-sm text-slate-700">
                <p id="sidebar-visit-date" class="rounded-full bg-white px-3 py-2 font-semibold text-slate-900">Belum dipilih</p>
                <p id="sidebar-visit-guests" class="rounded-full bg-white px-3 py-2 font-semibold text-slate-900">2 orang</p>
              </div>
            </div>
          </div>

          <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Paket</p>
            <div id="sidebar-main" class="mt-2 text-sm text-slate-600">Belum ada produk dipilih.</div>
          </div>

          <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Add-on</p>
            <div id="sidebar-addons" class="mt-2 text-sm text-slate-600">
              <p>Belum ada add-on dipilih.</p>
            </div>
          </div>

          <div class="mt-3 rounded-2xl bg-slate-900 p-4 text-white">
            <div class="flex items-center justify-between text-sm text-slate-300">
              <span>Estimasi subtotal</span>
              <span id="sidebar-subtotal">Rp 0</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-sm text-slate-300">
              <span>Service fee</span>
              <span id="sidebar-fee">Rp 0</span>
            </div>
            <div class="mt-4 border-t border-white/10 pt-4">
              <div class="flex items-center justify-between text-base font-bold">
                <span>Total</span>
                <span id="sidebar-total">Rp 0</span>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </section>
@endsection
