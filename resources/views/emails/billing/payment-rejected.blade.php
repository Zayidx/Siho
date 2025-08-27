<x-mail::message>
# Bukti Pembayaran Ditolak

Halo {{ $bill->reservation->guest->full_name ?? $bill->reservation->guest->username }},

Maaf, bukti pembayaran Anda untuk Invoice #{{ $bill->id }} ditolak setelah peninjauan. Silakan unggah kembali bukti yang valid atau hubungi kami untuk bantuan.

- Total: Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
- Metode: {{ $bill->payment_method ?? 'Bank Transfer' }}

Terima kasih,
{{ config('app.name') }}
</x-mail::message>
