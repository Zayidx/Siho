<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'discount_rate', 'apply_room_type_id', 'active', 'valid_from', 'valid_to', 'usage_limit', 'used_count',
    ];

    protected $casts = [
        'discount_rate' => 'decimal:2',
        'active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function scopeActiveValid($q)
    {
        $now = now();

        return $q->where('active', true)
            ->where(function ($qq) use ($now) {
                $qq->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($qq) use ($now) {
                $qq->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
            });
    }
}
