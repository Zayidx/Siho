<x-mail::message>
    # Pesan Kontak Baru

    Ada pesan baru yang dikirim melalui formulir kontak publik:

    - Nama: {{ $name }}
    - Email: {{ $email }}
    @if (!empty($subjectText))
        - Subjek: {{ $subjectText }}
    @endif
    @if (!empty($phone))
        - Telepon: {{ $phone }}
    @endif

    **Pesan:**

    {{ $text }}

</x-mail::message>
