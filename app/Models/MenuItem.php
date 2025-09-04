<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_category_id', 'name', 'description', 'price', 'is_active', 'is_popular', 'image'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'price' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        $p = $this->image;
        if (!$p) return null;
        return str_starts_with($p, 'http') ? $p : asset('storage/'.$p);
    }
}

