<x-mail::message>
    # Verifikasi Email Baru

    Halo {{ $name }},

    Klik tombol di bawah ini untuk memverifikasi alamat email baru Anda.

    <x-mail::button :url="$verifyUrl">
        Verifikasi Email
    </x-mail::button>

    Tautan ini berlaku sementara. Jika Anda tidak meminta perubahan email, abaikan pesan ini.

    Terima kasih,
    {{ config('app.name') }}
</x-mail::message>
