<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id', 'user_id', 'action', 'meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function bill()
    {
        return $this->belongsTo(Bills::class, 'bill_id');
    }
}

