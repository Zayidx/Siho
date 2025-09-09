<?php

return [
    // Tax rate for booking calculations (0.10 = 10%)
    'tax_rate' => (float) env('BOOKING_TAX_RATE', 0.10),

    // Flat service fee (in smallest currency unit)
    'service_fee' => (int) env('BOOKING_SERVICE_FEE', 50000),
];
