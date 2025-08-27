<x-mail::message>
# Pembayaran Berhasil

Terima kasih. Pembayaran untuk Invoice #{{ $bill->id }} telah kami terima.

- Total: Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
- Tanggal: {{ optional($bill->paid_at)->format('Y-m-d H:i') }}

Silakan lihat lampiran untuk salinan invoice Anda.

Terima kasih,
{{ config('app.name') }}
</x-mail::message>
