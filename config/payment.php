<?php

return [
    'bank' => [
        'name' => env('PAYMENT_BANK_NAME', 'BCA'),
        'account' => env('PAYMENT_BANK_ACCOUNT', '1234567890'),
        'holder' => env('PAYMENT_BANK_HOLDER', 'Grand Luxe Hotel'),
        'note' => env('PAYMENT_BANK_NOTE', 'Cantumkan kode referensi pada berita transfer.'),
    ],
    'qris' => [
        'enabled' => env('PAYMENT_QRIS_ENABLED', true),
        'image_url' => env('PAYMENT_QRIS_IMAGE_URL', 'https://upload.wikimedia.org/wikipedia/commons/d/d0/QR_code_for_mobile_English_Wikipedia.svg'),
        'note' => env('PAYMENT_QRIS_NOTE', 'Atau scan QR berikut untuk membayar.'),
    ],
];
