<x-mail::message>
    # Bukti Pembayaran F&B Ditolak

    Halo {{ $order->user->full_name ?? $order->user->username }},

    Maaf, bukti pembayaran Anda untuk pesanan F&B #{{ $order->id }} ditolak setelah peninjauan. Silakan unggah kembali
    bukti yang valid atau hubungi kami untuk bantuan.

    - Total: Rp {{ number_format($order->total_amount, 0, ',', '.') }}
    - Metode: {{ $order->payment_method ?? 'Bank Transfer' }}

    ## Instruksi Pembayaran (Transfer Bank)

    - Nama Bank: **{{ config('payment.bank.name') }}**
    - No. Rekening: **{{ implode(' ', str_split(config('payment.bank.account'), 4)) }}**
    - Atas Nama: **{{ config('payment.bank.holder') }}**
    - Kode Referensi: **FNB-{{ $order->id }}**

    {{ config('payment.bank.note') }}

    Terima kasih,
    {{ config('app.name') }}
</x-mail::message>

