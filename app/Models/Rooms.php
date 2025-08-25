<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rooms extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'room_number',
        'room_type',
        'status',
        'floor',
        'description',
        'price_per_night',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price_per_night' => 'decimal:2',
    ];

    /**
     * The reservations that belong to the room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
   public function reservations(): BelongsToMany
{
    // [PERBAIKAN] Beritahu Laravel nama kolom yang benar
    return $this->belongsToMany(Reservations::class, 'reservation_room', 'room_id', 'reservation_id')
                ->withTimestamps()->withPivot('assigned_at');
}
}