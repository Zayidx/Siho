<x-mail::message>
# Pembayaran Diterima

Halo {{ $bill->reservation->guest->full_name ?? $bill->reservation->guest->username }},

Pembayaran untuk Invoice #{{ $bill->id }} telah kami terima. Terima kasih!

- Total: Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
- Metode: {{ $bill->payment_method ?? 'Bank Transfer' }}
- Tanggal: {{ optional($bill->paid_at)->format('Y-m-d H:i') }}

Terima kasih telah melakukan pembayaran. Simpan email ini sebagai bukti penerimaan pembayaran Anda.

Salam,
{{ config('app.name') }}
</x-mail::message>
