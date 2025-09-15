<x-mail::message>
    # Status Pembayaran F&B Diperbarui

    Pesanan F&B #{{ $order->id }} telah diperbarui status pembayarannya menjadi: **{{ strtoupper($status) }}**.

    - Pelanggan: {{ $order->user->full_name ?? $order->user->username }} ({{ $order->user->email }})
    - Total: Rp {{ number_format($order->total_amount, 0, ',', '.') }}
    - Metode: {{ $order->payment_method ?? '-' }}
    - Diunggah: {{ optional($order->payment_proof_uploaded_at)->format('Y-m-d H:i') ?? '-' }}

    Silakan tinjau di halaman kasir bila diperlukan.

    @if ($status !== 'approved')
        ## Instruksi Pembayaran (Transfer Bank)

        - Nama Bank: **{{ config('payment.bank.name') }}**
        - No. Rekening: **{{ implode(' ', str_split(config('payment.bank.account'), 4)) }}**
        - Atas Nama: **{{ config('payment.bank.holder') }}**
        - Kode Referensi: **FNB-{{ $order->id }}**

        {{ config('payment.bank.note') }}
    @endif

    Terima kasih,
    {{ config('app.name') }}
</x-mail::message>

