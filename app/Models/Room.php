<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Room extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'room_number',
        'room_type_id',
        'status',
        'floor',
        'description',
        'personalized_facilities',
        'price_per_night',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price_per_night' => 'decimal:2',
        'personalized_facilities' => 'array',
    ];

    /**
     * Get the type of the room.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    /**
     * The reservations that belong to the room.
     */
    public function reservations(): BelongsToMany
    {
        // [PERBAIKAN] Beritahu Laravel nama kolom yang benar
        return $this->belongsToMany(Reservation::class, 'reservation_room', 'room_id', 'reservation_id')
            ->withTimestamps()->withPivot('assigned_at');
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class, 'room_id')->orderBy('sort_order');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\RoomItemInventory::class, 'room_id');
    }
}
