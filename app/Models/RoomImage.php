<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id', // legacy (nullable)
        'room_type_id',
        'path',
        'sort_order',
        'is_cover',
    ];

    public function room()
    {
        return $this->belongsTo(Rooms::class, 'room_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }
}
