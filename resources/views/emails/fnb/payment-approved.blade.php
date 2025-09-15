<x-mail::message>
    # Pembayaran F&B Diterima

    Halo {{ $order->user->full_name ?? $order->user->username }},

    Pembayaran untuk pesanan F&B #{{ $order->id }} telah kami terima. Terima kasih!

    - Total: Rp {{ number_format($order->total_amount, 0, ',', '.') }}
    - Metode: {{ $order->payment_method ?? 'Bank Transfer' }}
    - Tanggal: {{ optional($order->updated_at)->format('Y-m-d H:i') }}

    Kami akan segera melanjutkan proses pesanan Anda.

    Salam,
    {{ config('app.name') }}
</x-mail::message>

