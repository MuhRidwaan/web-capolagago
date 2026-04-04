<x-mail::message>
# Pembayaran Berhasil! 🎉

Halo **{{ $booking->user_name ?? 'Pelanggan' }}**,

Terima kasih telah melakukan pemesanan di **{{ $appName }}**.
Pembayaran kamu telah kami terima dan booking kamu sudah **terkonfirmasi**.

---

<x-mail::panel>
**Detail Booking**

| | |
|---|---|
| Kode Booking | **{{ $booking->booking_code }}** |
| Tanggal Kunjungan | {{ \Carbon\Carbon::parse($booking->visit_date)->translatedFormat('l, d F Y') }} |
| Jumlah Tamu | {{ $booking->total_guests }} orang |
| Total Pembayaran | **Rp {{ number_format($payment->amount, 0, ',', '.') }}** |
| Metode Pembayaran | {{ $payment->payment_method_name ?? '-' }} |
| Waktu Pembayaran | {{ \Carbon\Carbon::parse($payment->paid_at)->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB |
</x-mail::panel>

Tunjukkan kode booking ini saat check-in:

<x-mail::panel>
# {{ $booking->booking_code }}
</x-mail::panel>

Jika ada pertanyaan, hubungi kami melalui WhatsApp atau balas email ini.

Sampai jumpa di **{{ $appName }}**! 🏕️

<x-mail::button :url="$appUrl" color="success">
Kunjungi Website
</x-mail::button>

Salam hangat,
Tim {{ $appName }}

---
<small>Email ini dikirim otomatis. Jangan membalas jika tidak ada pertanyaan.</small>
</x-mail::message>
