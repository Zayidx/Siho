<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $bill->id }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #333; }
        .header { margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .section { margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
        .right { text-align: right; }
    </style>
    </head>
<body>
    <div class="header">
        <div class="title">Invoice #{{ $bill->id }}</div>
        <div>Tanggal Terbit: {{ optional($bill->issued_at)->format('Y-m-d H:i') }}</div>
        <div>Status: {{ $bill->paid_at ? 'Paid' : 'Unpaid' }}</div>
    </div>

    <div class="section">
        <strong>Pelanggan</strong>
        <div>Nama: {{ $bill->reservation->guest->full_name ?? $bill->reservation->guest->username }}</div>
        <div>Email: {{ $bill->reservation->guest->email }}</div>
    </div>

    <div class="section">
        <strong>Reservasi</strong>
        <div>ID Reservasi: #{{ $bill->reservation->id }}</div>
        <div>Check-in: {{ $bill->reservation->check_in_date->format('Y-m-d') }}</div>
        <div>Check-out: {{ $bill->reservation->check_out_date->format('Y-m-d') }}</div>
        <div>Kamar: {{ $bill->reservation->rooms->pluck('room_number')->join(', ') }}</div>
    </div>

    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th class="right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Subtotal</td>
                    <td class="right">Rp {{ number_format($bill->subtotal_amount ?? $bill->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Diskon</td>
                    <td class="right">- Rp {{ number_format($bill->discount_amount ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Pajak (10%)</td>
                    <td class="right">Rp {{ number_format($bill->tax_amount ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Biaya Layanan</td>
                    <td class="right">Rp {{ number_format($bill->service_fee_amount ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <th class="right">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</th>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div>Metode Pembayaran: {{ $bill->payment_method ?? '-' }}</div>
        <div>Dibayar pada: {{ optional($bill->paid_at)->format('Y-m-d H:i') ?? '-' }}</div>
    </div>
</body>
</html>
