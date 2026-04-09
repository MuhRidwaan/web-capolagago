@extends('frontend.layouts.main')

@section('title', 'CapolagaGo - Detail Booking Produk')
@section('meta_description', 'Pilih tanggal kunjungan dan cek ketersediaan slot sebelum melanjutkan booking produk.')

@section('content')
@php
    $today = now()->format('Y-m-d');
    $productImage = $product->primary_image_url;
    $productPayload = [
        'id' => $product->id,
        'slug' => $product->slug,
        'name' => $product->name,
    ];

    if (! $productImage) {
        $slug = strtolower((string) $product->slug);
        $category = strtolower((string) ($product->category?->label ?? ''));

        $productImage = match (true) {
            str_contains($slug, 'glamping') || str_contains($category, 'glamping') => asset('images/glamping.jpg'),
            str_contains($slug, 'camping') || str_contains($category, 'camping') => asset('images/camping.jpg'),
            str_contains($slug, 'homestay') || str_contains($category, 'homestay') => asset('images/homestay.jpg'),
            default => asset('images/glamping.jpg'),
        };
    }
@endphp

<section class="border-b border-slate-200 bg-white pt-8">
    <div class="mx-auto max-w-[1680px] px-4 py-6 sm:px-6 lg:px-8">
        <div class="max-w-4xl">
            <a href="{{ route('ticket.booking') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 transition hover:text-slate-900">
                <span aria-hidden="true">&larr;</span>
                <span>Kembali ke daftar produk</span>
            </a>
            <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Pesan {{ $product->name }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                Pilih tanggal kunjungan dan jumlah peserta, lalu cek ketersediaan slot sebelum melanjutkan ke booking utama.
            </p>
        </div>
    </div>
</section>

<section class="bg-[#f8fafc] pb-12 pt-6 md:pt-8">
    <div class="mx-auto grid max-w-[1680px] gap-8 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:px-8">
        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 sm:flex-wrap sm:overflow-visible sm:px-0 sm:pb-0">
                    <span class="shrink-0 rounded-2xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 sm:px-4 sm:py-2.5 sm:text-sm">1. Pilih Produk</span>
                    <span class="shrink-0 rounded-2xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-900 sm:px-4 sm:py-2.5 sm:text-sm">2. Tanggal &amp; Slot</span>
                    <span class="shrink-0 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500 sm:px-4 sm:py-2.5 sm:text-sm">3. Add-on</span>
                    <span class="shrink-0 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500 sm:px-4 sm:py-2.5 sm:text-sm">4. Keranjang</span>
                    <span class="shrink-0 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500 sm:px-4 sm:py-2.5 sm:text-sm">5. Checkout</span>
                    <span class="shrink-0 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500 sm:px-4 sm:py-2.5 sm:text-sm">6. Konfirmasi</span>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="grid gap-5 xl:grid-cols-[180px_minmax(0,1fr)]">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-100">
                    <img src="{{ $productImage }}" alt="{{ $product->name }}" class="h-56 w-full object-cover sm:h-72 xl:h-full" />
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-800">
                            {{ $product->category?->label ?? 'Produk' }}
                        </span>
                        @if ($product->is_featured)
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Featured</span>
                        @endif
                    </div>
                    <h2 class="mt-4 text-2xl font-bold leading-tight text-slate-900 sm:text-3xl">{{ $product->name }}</h2>
                    <p class="mt-4 text-sm leading-6 text-slate-600 sm:leading-7">{{ $product->short_desc ?: 'Deskripsi singkat belum tersedia.' }}</p>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
                            Harga: Rp {{ number_format((float) $product->price, 0, ',', '.') }}{{ $product->price_label }}
                        </span>
                        <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
                            Pax: {{ $product->min_pax }}-{{ $product->max_pax }} tamu
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t border-slate-200 pt-6">
                <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_220px] md:items-end">
                    <div class="block">
                        <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Tanggal Kunjungan</span>
                        <button type="button" id="calendar-trigger" class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm text-slate-700 outline-none transition hover:border-emerald-300 focus:border-emerald-400">
                            <span id="calendar-trigger-label">{{ $prefilledVisitDate ?: '-- Pilih Tanggal --' }}</span>
                            <span class="text-emerald-700" aria-hidden="true">📅</span>
                        </button>
                        <input id="visit-date-input" type="date" value="{{ $prefilledVisitDate }}" min="{{ $today }}" class="sr-only" />
                    </div>
                    <label class="block">
                        <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Jumlah Peserta</span>
                        <input id="guest-input" type="number" min="1" value="{{ $prefilledGuests }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-400" />
                    </label>
                </div>
            </div>

            <div id="calendar-panel" class="mt-4 hidden rounded-3xl border border-slate-200 bg-slate-50 p-3 sm:p-3.5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Kalender Kuota</p>
                        <h3 id="calendar-label" class="mt-1 text-base font-bold text-slate-900">Memuat kalender...</h3>
                        <p class="mt-1 text-[11px] text-slate-600 sm:text-xs">Klik tanggal untuk memilih kunjungan dan lihat sisa kuota.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="calendar-prev-button" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 bg-white text-xs text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                            &larr;
                        </button>
                        <button type="button" id="calendar-next-button" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 bg-white text-xs text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                            &rarr;
                        </button>
                    </div>
                </div>

                <div id="calendar-status" class="mt-3 hidden rounded-2xl border px-3 py-2.5 text-xs"></div>

                <div class="mt-3 overflow-x-auto">
                    <div class="min-w-[560px] overflow-hidden rounded-2xl border border-slate-200 bg-white sm:min-w-0">
                        <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 text-center text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                            <div class="px-1 py-1.5">Min</div>
                            <div class="px-1 py-1.5">Sen</div>
                            <div class="px-1 py-1.5">Sel</div>
                            <div class="px-1 py-1.5">Rab</div>
                            <div class="px-1 py-1.5">Kam</div>
                            <div class="px-1 py-1.5">Jum</div>
                            <div class="px-1 py-1.5">Sab</div>
                        </div>
                        <div id="calendar-grid" class="grid grid-cols-7"></div>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-1.5 text-[10px] font-medium text-slate-600">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2 py-1">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Tersedia
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2 py-1">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        Kuota tipis / penuh
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2 py-1">
                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                        Diblokir
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2 py-1">
                        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                        Belum diatur
                    </span>
                </div>
            </div>

            <div id="availability-feedback" class="mt-5 hidden rounded-2xl border px-4 py-3 text-sm"></div>

            <div class="mt-6 flex justify-end">
                <a id="continue-booking-link" href="{{ route('ticket.booking', ['product' => $product->slug, 'date' => $prefilledVisitDate, 'guests' => $prefilledGuests]) }}" aria-disabled="true" class="pointer-events-none inline-flex w-full items-center justify-center rounded-2xl bg-slate-300 px-5 py-3 text-sm font-semibold text-white transition sm:w-auto">
                    Lanjut ke Booking
                </a>
            </div>
            </section>
        </div>

        <aside class="h-fit rounded-3xl border border-slate-200 bg-white p-4 shadow-sm lg:sticky lg:top-24">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Ringkasan Pesanan</p>
            <div class="mt-4 rounded-2xl bg-slate-50 p-3">
                <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $product->category?->label ?? 'Produk' }}</p>
                <p class="mt-3 text-sm font-semibold text-emerald-700">Rp {{ number_format((float) $product->price, 0, ',', '.') }}{{ $product->price_label }}</p>
            </div>

            <div class="mt-4 space-y-3 border-t border-slate-200 pt-4 text-sm text-slate-700">
                <div class="flex items-center justify-between gap-3">
                    <span>Tanggal</span>
                    <strong id="summary-date" class="text-slate-900">{{ $prefilledVisitDate }}</strong>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span>Peserta</span>
                    <strong id="summary-guests" class="text-slate-900">{{ $prefilledGuests }} orang</strong>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span>Status slot</span>
                    <span id="summary-status" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Belum dicek</span>
                </div>
            </div>
        </aside>
    </div>
</section>

<script>
    (() => {
        const availabilityUrl = @json(route('ticket.booking.availability', [], false));
        const calendarUrl = @json(route('ticket.booking.product.calendar', ['slug' => $product->slug], false));
        const continueBookingBaseUrl = @json(route('ticket.booking', [], false));
        const product = @json($productPayload);

        const visitDateInput = document.getElementById('visit-date-input');
        const calendarTrigger = document.getElementById('calendar-trigger');
        const calendarTriggerLabel = document.getElementById('calendar-trigger-label');
        const calendarPanel = document.getElementById('calendar-panel');
        const guestInput = document.getElementById('guest-input');
        const availabilityFeedback = document.getElementById('availability-feedback');
        const continueBookingLink = document.getElementById('continue-booking-link');
        const summaryDate = document.getElementById('summary-date');
        const summaryGuests = document.getElementById('summary-guests');
        const summaryStatus = document.getElementById('summary-status');
        const calendarLabel = document.getElementById('calendar-label');
        const calendarGrid = document.getElementById('calendar-grid');
        const calendarStatus = document.getElementById('calendar-status');
        const calendarPrevButton = document.getElementById('calendar-prev-button');
        const calendarNextButton = document.getElementById('calendar-next-button');

        const state = {
            month: (visitDateInput.value || new Date().toISOString().slice(0, 7)).slice(0, 7),
            calendar: null,
            calendarOpen: Boolean(visitDateInput.value),
            availability: null,
        };

        const formatDisplayDate = (value) => {
            if (!value) {
                return '-- Pilih Tanggal --';
            }

            const date = new Date(`${value}T00:00:00`);

            return new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
            }).format(date);
        };

        const pad = (value) => String(value).padStart(2, '0');
        const getMonthLabel = (monthValue) => {
            const [year, month] = monthValue.split('-').map(Number);
            const date = new Date(year, month - 1, 1);

            return new Intl.DateTimeFormat('id-ID', {
                month: 'long',
                year: 'numeric',
            }).format(date);
        };

        const shiftMonth = (monthValue, delta) => {
            const [year, month] = monthValue.split('-').map(Number);
            const date = new Date(year, month - 1 + delta, 1);

            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}`;
        };

        const syncCalendarVisibility = () => {
            calendarPanel.classList.toggle('hidden', !state.calendarOpen);
        };

        const syncSummary = () => {
            summaryDate.textContent = visitDateInput.value || 'Belum dipilih';
            summaryGuests.textContent = `${guestInput.value || 1} orang`;
            calendarTriggerLabel.textContent = formatDisplayDate(visitDateInput.value);

            const params = new URLSearchParams({
                product: product.slug,
                date: visitDateInput.value || '',
                guests: guestInput.value || '1',
            });
            continueBookingLink.href = `${continueBookingBaseUrl}?${params.toString()}`;
        };

        const updateContinueButton = () => {
            const canContinue = Boolean(
                state.availability?.available
                && state.availability?.slot
                && state.availability.slot.slot_date === visitDateInput.value
            );

            continueBookingLink.setAttribute('aria-disabled', canContinue ? 'false' : 'true');
            continueBookingLink.className = canContinue
                ? 'inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto'
                : 'pointer-events-none inline-flex w-full items-center justify-center rounded-2xl bg-slate-300 px-5 py-3 text-sm font-semibold text-white transition sm:w-auto';
        };

        const setFeedback = (message, tone = 'info') => {
            const tones = {
                info: 'border-emerald-200 bg-emerald-50 text-emerald-900',
                warning: 'border-amber-200 bg-amber-50 text-amber-900',
                danger: 'border-rose-200 bg-rose-50 text-rose-900',
            };

            availabilityFeedback.className = `mt-5 rounded-2xl border px-4 py-3 text-sm ${tones[tone] || tones.info}`;
            availabilityFeedback.textContent = message;
            availabilityFeedback.classList.remove('hidden');
        };

        const setCalendarStatus = (message, tone = 'info') => {
            const tones = {
                info: 'border-emerald-200 bg-emerald-50 text-emerald-900',
                warning: 'border-amber-200 bg-amber-50 text-amber-900',
                danger: 'border-rose-200 bg-rose-50 text-rose-900',
            };

            calendarStatus.className = `mt-4 rounded-2xl border px-4 py-3 text-sm ${tones[tone] || tones.info}`;
            calendarStatus.textContent = message;
            calendarStatus.classList.remove('hidden');
        };

        const getDayToneClasses = (day) => {
            if (day.is_past) {
                return {
                    button: 'cursor-not-allowed border-slate-100 bg-slate-50 text-slate-300',
                    badge: 'bg-slate-200 text-slate-500',
                    meta: 'text-slate-400',
                };
            }

            if (day.status === 'blocked') {
                return {
                    button: 'border-rose-200 bg-rose-50 text-rose-900 hover:border-rose-300',
                    badge: 'bg-rose-100 text-rose-800',
                    meta: 'text-rose-700',
                };
            }

            if (day.status === 'full') {
                return {
                    button: 'border-amber-200 bg-amber-50 text-amber-900 hover:border-amber-300',
                    badge: 'bg-amber-100 text-amber-800',
                    meta: 'text-amber-700',
                };
            }

            if (day.status === 'default') {
                return {
                    button: 'cursor-not-allowed border-slate-200 bg-slate-50 text-slate-500',
                    badge: 'bg-slate-200 text-slate-700',
                    meta: 'text-slate-500',
                };
            }

            return {
                button: 'border-emerald-200 bg-emerald-50 text-emerald-900 hover:border-emerald-400',
                badge: 'bg-emerald-100 text-emerald-800',
                meta: 'text-emerald-700',
            };
        };

        const renderCalendar = () => {
            const calendar = state.calendar;

            if (!calendar) {
                calendarLabel.textContent = getMonthLabel(state.month);
                calendarGrid.innerHTML = `
                    <div class="col-span-7 p-6 text-center text-sm text-slate-500">
                        Kalender kuota belum tersedia.
                    </div>
                `;
                return;
            }

            calendarLabel.textContent = calendar.label;

            const firstDate = new Date(`${calendar.month}-01T00:00:00`);
            const firstWeekday = firstDate.getDay();
            const leadingEmptyCells = Array.from({ length: firstWeekday }, () => {
                return '<div class="min-h-[60px] border-b border-r border-slate-100 bg-slate-50/70"></div>';
            }).join('');

            const dayCells = calendar.days.map((day) => {
                const tones = getDayToneClasses(day);
                const isSelected = visitDateInput.value === day.date;
                const isClickable = day.status === 'available' && !day.is_past;
                const isDisabled = !isClickable;
                const buttonStateClass = isSelected
                    ? 'ring-2 ring-emerald-400 ring-offset-2 ring-offset-white'
                    : '';
                const quotaLabel = day.status === 'blocked'
                    ? 'Diblokir'
                    : day.status === 'full'
                        ? 'Penuh'
                        : day.status === 'default'
                            ? '0'
                            : `${day.remaining_capacity}`;

                return `
                    <button
                        type="button"
                        data-calendar-date="${day.date}"
                        ${isDisabled ? 'disabled' : ''}
                        class="min-h-[60px] border-b border-r border-slate-100 p-1 text-left align-top transition ${tones.button} ${buttonStateClass}"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-[10px] font-bold">${day.day}</span>
                            <span class="rounded-full px-1.5 py-0.5 text-[8px] font-semibold ${tones.badge}">
                                ${quotaLabel}
                            </span>
                        </div>
                        <p class="mt-1 text-[9px] leading-3.5 font-medium ${tones.meta}">
                            ${day.status === 'available'
                                ? `Kuota tersedia: ${day.remaining_capacity}`
                                : day.status === 'full'
                                    ? 'Kuota habis'
                                    : day.status === 'blocked'
                                        ? 'Slot ditutup'
                                        : day.is_past
                                            ? 'Tanggal lewat'
                                            : '0'}
                        </p>
                    </button>
                `;
            }).join('');

            calendarGrid.innerHTML = leadingEmptyCells + dayCells;

            calendarGrid.querySelectorAll('[data-calendar-date]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const nextDate = button.dataset.calendarDate;
                    visitDateInput.value = nextDate;
                    state.calendarOpen = false;
                    state.availability = null;
                    syncSummary();
                    syncCalendarVisibility();
                    renderCalendar();
                    updateContinueButton();
                    calendarTrigger.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    await checkAvailability();
                });
            });
        };

        const loadCalendar = async () => {
            calendarGrid.innerHTML = `
                <div class="col-span-7 p-6 text-center text-sm text-slate-500">
                    Memuat kuota per tanggal...
                </div>
            `;
            calendarLabel.textContent = getMonthLabel(state.month);

            try {
                const response = await fetch(`${calendarUrl}?month=${encodeURIComponent(state.month)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Kalender kuota belum bisa dimuat.');
                }

                state.calendar = result;
                renderCalendar();
                setCalendarStatus('Kalender kuota berhasil dimuat. Hanya tanggal yang tersedia yang bisa dipilih.', 'info');
            } catch (error) {
                state.calendar = null;
                renderCalendar();
                setCalendarStatus(error.message || 'Kalender kuota belum bisa dimuat.', 'danger');
            }
        };

        const renderAvailability = (result) => {
            const badgeClass = result.available
                ? 'bg-emerald-100 text-emerald-800'
                : 'bg-amber-100 text-amber-800';

            summaryStatus.className = `rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}`;
            summaryStatus.textContent = result.available ? 'Tersedia' : 'Perlu perhatian';
        };

        const checkAvailability = async () => {
            const params = new URLSearchParams({
                product_id: product.id,
                visit_date: visitDateInput.value,
                total_guests: guestInput.value,
            });

            if (!visitDateInput.value || !guestInput.value) {
                state.availability = null;
                summaryStatus.className = 'rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600';
                summaryStatus.textContent = 'Belum dicek';
                setFeedback('Pilih tanggal kunjungan dan jumlah peserta untuk melihat ketersediaan slot.', 'info');
                updateContinueButton();

                return;
            }

            try {
                const response = await fetch(`${availabilityUrl}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json();
                state.availability = result;
                renderAvailability(result);
                setFeedback(result.message, result.available ? 'info' : 'warning');
                updateContinueButton();
            } catch (error) {
                state.availability = null;
                summaryStatus.className = 'rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800';
                summaryStatus.textContent = 'Gagal dicek';
                setFeedback('Gagal mengecek ketersediaan slot saat ini.', 'danger');
                updateContinueButton();
            }
        };

        visitDateInput.addEventListener('input', async () => {
            const nextMonth = (visitDateInput.value || state.month).slice(0, 7);
            state.availability = null;
            summaryStatus.className = 'rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600';
            summaryStatus.textContent = 'Belum dicek';
            syncSummary();
            updateContinueButton();

            if (nextMonth !== state.month) {
                state.month = nextMonth;
                await loadCalendar();
                await checkAvailability();
                return;
            }

            renderCalendar();
            await checkAvailability();
        });
        calendarTrigger.addEventListener('click', async () => {
            state.calendarOpen = !state.calendarOpen;
            syncCalendarVisibility();

            if (state.calendarOpen && !state.calendar) {
                await loadCalendar();
            }
        });
        guestInput.addEventListener('input', () => {
            state.availability = null;
            summaryStatus.className = 'rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600';
            summaryStatus.textContent = 'Belum dicek';
            syncSummary();
            updateContinueButton();
        });
        guestInput.addEventListener('change', async () => {
            await checkAvailability();
        });
        calendarPrevButton.addEventListener('click', () => {
            state.month = shiftMonth(state.month, -1);
            loadCalendar();
        });
        calendarNextButton.addEventListener('click', () => {
            state.month = shiftMonth(state.month, 1);
            loadCalendar();
        });

        syncSummary();
        updateContinueButton();
        syncCalendarVisibility();
        if (state.calendarOpen) {
            loadCalendar();
        }
        if (visitDateInput.value && guestInput.value) {
            checkAvailability();
        }
    })();
</script>
@endsection
