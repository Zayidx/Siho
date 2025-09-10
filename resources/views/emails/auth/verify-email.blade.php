<x-mail::message>
    # Verifikasi Email

    Halo {{ $name }},

    Klik tombol berikut untuk memverifikasi alamat email Anda.

    <x-mail::button :url="$verifyUrl">
        Verifikasi Email
    </x-mail::button>

    Jika Anda tidak meminta verifikasi ini, abaikan email ini.

    Terima kasih,
    {{ config('app.name') }}
</x-mail::message>
