<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Invoice {{ $booking->booking_code }} - CapolagaGo</title>
    <style>
        :root {
            color-scheme: light;
            --green: #21885a;
            --green-dark: #155c3d;
            --border: #d9e3dc;
            --muted: #64748b;
            --text: #1f2937;
            --paper: #ffffff;
            --bg: #f3f6f4;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Georgia, "Times New Roman", serif;
        }
        .shell {
            max-width: 920px;
            margin: 0 auto;
            padding: 28px 20px 40px;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            font-family: Arial, sans-serif;
        }
        .toolbar a,
        .toolbar button {
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }
        .paper {
            background: var(--paper);
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
            padding: 36px 42px 40px;
        }
        .topline {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            align-items: flex-start;
        }
        .brand {
            color: var(--green);
            font-size: 24px;
            font-weight: 700;
            line-height: 1.1;
        }
        .subtitle,
        .meta,
        .note,
        .bank {
            font-family: Arial, sans-serif;
        }
        .subtitle {
            margin-top: 8px;
            color: var(--muted);
            font-size: 14px;
        }
        .invoice-title {
            text-align: right;
            font-family: Arial, sans-serif;
        }
        .invoice-title h1 {
            margin: 0;
            font-size: 30px;
            color: #111827;
        }
        .invoice-title p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 14px;
        }
        .divider {
            height: 2px;
            background: linear-gradient(90deg, var(--green) 0%, rgba(33,136,90,0.15) 100%);
            margin: 20px 0 24px;
        }
        .summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 24px;
            font-family: Arial, sans-serif;
        }
        .card {
            padding: 0;
        }
        .label {
            color: #4b5563;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .value-strong {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }
        .small {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid var(--border);
            padding: 12px 10px;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }
        th {
            background: #f8fbf9;
            font-weight: 700;
        }
        td.num, th.num { text-align: right; }
        .bottom {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(260px, 0.8fr);
            gap: 22px;
            margin-top: 28px;
        }
        .note-box {
            border: 1px dashed #ead69a;
            background: #fff9e8;
            border-radius: 14px;
            padding: 14px 16px;
        }
        .note-box .label {
            color: #9a6b00;
            margin-bottom: 8px;
        }
        .totals {
            font-family: Arial, sans-serif;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 8px 0;
            font-size: 15px;
            color: #374151;
        }
        .totals-row.total {
            margin-top: 6px;
            padding-top: 14px;
            border-top: 1px solid var(--border);
            font-size: 18px;
            font-weight: 700;
            color: var(--green);
        }
        .bank {
            margin-top: 22px;
            line-height: 1.8;
            font-size: 14px;
        }
        .muted { color: var(--muted); }

        @media print {
            body { background: #fff; }
            .shell { padding: 0; max-width: none; }
            .toolbar { display: none; }
            .paper {
                border-radius: 0;
                box-shadow: none;
                padding: 22px 28px 28px;
            }
        }
    </style>
</head>
<body>
@php
    $statusLabel = $statusLabels[$payment->status ?? 'pending'] ?? ucfirst((string) ($payment->status ?? 'pending'));
@endphp
<div class="shell">
    <div class="toolbar">
        <a href="{{ route('ticket.booking.status', ['token' => $booking->public_token]) }}">Kembali ke Status</a>
        <button type="button" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="paper">
        <div class="topline">
            <div>
                <div class="brand">CapolagaGo</div>
                <div class="subtitle">Invoice Resmi Pembayaran</div>
            </div>
            <div class="invoice-title">
                <h1>Invoice #{{ $booking->booking_code }}</h1>
                <p>Dicetak: {{ $printedAt->translatedFormat('d M Y') }}</p>
            </div>
        </div>

        <div class="divider"></div>

        <div class="summary">
            <div class="card">
                <div class="label">Dibayar Oleh</div>
                <div class="value-strong">{{ $booking->customer_name }}</div>
                <div class="small">{{ $booking->customer_email }}</div>
                <div class="small">{{ \Illuminate\Support\Carbon::parse($booking->visit_date)->translatedFormat('d M Y') }} • {{ $booking->total_guests }} Pax</div>
            </div>
            <div class="card" style="text-align:right;">
                <div class="label">Status Pembayaran</div>
                <div class="value-strong" style="font-size:20px;">{{ $statusLabel }}</div>
                <div class="small">Metode: {{ $payment->payment_method_name ?? 'Metode Pembayaran' }}</div>
                <div class="small">Referensi: {{ $payment->va_number ?: ($payment->payment_code ?? '-') }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Deskripsi Paket Tour</th>
                    <th class="num">Peserta</th>
                    <th class="num">Harga Paket</th>
                    <th class="num">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->product_name_snapshot }}{{ $item->is_addon ? ' (Add-on)' : '' }}</td>
                        <td class="num">{{ number_format((float) $item->quantity, 0, ',', '.') }}</td>
                        <td class="num">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                        <td class="num">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="bottom">
            <div>
                <div class="note-box">
                    <div class="label">Catatan</div>
                    <div class="note">
                        Harap simpan bukti ini dan tunjukkan pada petugas saat daftar ulang di lokasi wisata.
                    </div>
                </div>

                <div class="bank">
                    <strong>Transfer / Referensi Pembayaran</strong><br>
                    Bank / Metode: {{ $payment->payment_method_name ?? '-' }}<br>
                    No. Ref / VA: {{ $payment->va_number ?: ($payment->payment_code ?? '-') }}<br>
                    Status Saat Ini: {{ $statusLabel }}<br><br>
                    <span class="muted">Silakan selesaikan pembayaran sesuai total tagihan lalu simpan invoice ini untuk keperluan verifikasi.</span>
                </div>
            </div>

            <div class="totals">
                <div class="totals-row">
                    <span>Subtotal Paket Tour:</span>
                    <strong>Rp {{ number_format((float) $booking->subtotal, 0, ',', '.') }}</strong>
                </div>
                <div class="totals-row">
                    <span>Service Fee:</span>
                    <strong>Rp {{ number_format((float) $booking->service_fee, 0, ',', '.') }}</strong>
                </div>
                <div class="totals-row total">
                    <span>Total Bayar:</span>
                    <span>Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
