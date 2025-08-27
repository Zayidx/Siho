<x-mail::message>
# Bukti Pembayaran Diupload

Seorang pengguna telah mengunggah bukti pembayaran untuk tagihan berikut:

- ID Tagihan: {{ $bill->id }}
- ID Reservasi: {{ $bill->reservation_id }}
- Tamu: {{ $bill->reservation->guest->full_name ?? $bill->reservation->guest->username }} ({{ $bill->reservation->guest->email }})
- Total: Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
- Metode: {{ $bill->payment_method ?? 'Bank Transfer' }}
- Waktu Unggah: {{ optional($bill->payment_proof_uploaded_at)->format('Y-m-d H:i') }}

Silakan tinjau di halaman admin: Pembayaran.

Terima kasih,
{{ config('app.name') }}
</x-mail::message>
