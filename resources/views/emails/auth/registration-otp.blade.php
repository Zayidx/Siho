<x-mail::message>
    # Kode OTP Pendaftaran

    Gunakan kode berikut untuk menyelesaikan proses pendaftaran Anda. Kode berlaku 5 menit.

    <x-mail::panel>
        <div style="text-align:center; font-size: 24px; font-weight: 700; letter-spacing: 4px;">
            {{ $otpCode }}
        </div>
    </x-mail::panel>

    Jika Anda tidak meminta kode ini, abaikan email ini.

    Terima kasih,
    {{ config('app.name') }}

</x-mail::message>
