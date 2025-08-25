<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservations extends Model
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    /**
     * Get the bill associated with the reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function bill(): HasOne
    {
        return $this->hasOne(Bills::class);
    }

    /**
     * The rooms that belong to the reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function rooms(): BelongsToMany
{
    // [PERBAIKAN] Beritahu Laravel nama kolom yang benar
    return $this->belongsToMany(Rooms::class, 'reservation_room', 'reservation_id', 'room_id')
                ->withTimestamps()->withPivot('assigned_at');
}

}
