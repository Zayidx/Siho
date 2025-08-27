<x-mail::message>
# Status Pembayaran Diperbarui

Tagihan #{{ $bill->id }} telah diperbarui statusnya menjadi: **{{ strtoupper($status) }}**.

- Tamu: {{ $bill->reservation->guest->full_name ?? $bill->reservation->guest->username }} ({{ $bill->reservation->guest->email }})
- Total: Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
- Metode: {{ $bill->payment_method ?? '-' }}
- Dibayar: {{ optional($bill->paid_at)->format('Y-m-d H:i') ?? '-' }}

Silakan tinjau di halaman admin jika diperlukan.

Terima kasih,
{{ config('app.name') }}
</x-mail::message>
