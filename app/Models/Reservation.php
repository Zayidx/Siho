<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'guest_id',
        'check_in_date',
        'check_out_date',
        'status',
        'special_requests',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
    ];

    /**
     * Get the guest that owns the reservation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    /**
     * Get the bill associated with the reservation.
     */
    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class, 'reservation_id', 'id');
    }

    /**
     * The rooms that belong to the reservation.
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'reservation_room', 'reservation_id', 'room_id')
            ->withTimestamps()
            ->withPivot('assigned_at');
    }
}
