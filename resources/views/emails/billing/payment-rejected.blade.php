<x-mail::message>
# Bukti Pembayaran Ditolak

Halo {{ $bill->reservation->guest->full_name ?? $bill->reservation->guest->username }},

Maaf, bukti pembayaran Anda untuk Invoice #{{ $bill->id }} ditolak setelah peninjauan. Silakan unggah kembali bukti yang valid atau hubungi kami untuk bantuan.

- Total: Rp {{ number_format($bill->total_amount, 0, ',', '.') }}  
- Metode: {{ $bill->payment_method ?? 'Bank Transfer' }}

## Instruksi Pembayaran (Transfer Bank)

- Nama Bank: **{{ config('payment.bank.name') }}**  
- No. Rekening: **{{ implode(' ', str_split(config('payment.bank.account'), 4)) }}**  
- Atas Nama: **{{ config('payment.bank.holder') }}**  
- Kode Referensi: **INV-{{ $bill->id }}**  

{{ config('payment.bank.note') }}

Terima kasih,
{{ config('app.name') }}
</x-mail::message>
