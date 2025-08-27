<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bills extends Model
{
     use HasFactory;

    protected $fillable = [
        'reservation_id', // Pastikan ini benar
        'total_amount',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'service_fee_amount',
        'issued_at',
        'paid_at',
        'payment_method',
        'notes',
        'payment_proof_path',
        'payment_review_status',
        'payment_proof_uploaded_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_fee_amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'payment_proof_uploaded_at' => 'datetime',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservations::class, 'reservation_id');
    }

    public function logs()
    {
        return $this->hasMany(PaymentLog::class, 'bill_id')->latest();
    }
}
