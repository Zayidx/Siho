<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FnbOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'menu_item_id', 'qty', 'unit_price', 'line_total',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'integer',
        'line_total' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(FnbOrder::class, 'order_id');
    }

    public function item()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }
}
