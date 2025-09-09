<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FnbOrder extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_READY = 'ready';

    public const STATUS_SERVED = 'served';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PAID = 'paid';

    public const SERVICE_IN_ROOM = 'in_room';

    public const SERVICE_DINE_IN = 'dine_in';

    public const SERVICE_TAKEAWAY = 'takeaway';

    public const ALLOWED_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PREPARING,
        self::STATUS_READY,
        self::STATUS_SERVED,
        self::STATUS_CANCELLED,
    ];

    public const ALLOWED_SERVICE_TYPES = [
        self::SERVICE_IN_ROOM,
        self::SERVICE_DINE_IN,
        self::SERVICE_TAKEAWAY,
    ];

    protected $fillable = [
        'user_id', 'status', 'payment_status', 'service_type', 'total_amount', 'notes', 'room_number',
    ];

    protected $casts = [
        'total_amount' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(FnbOrderItem::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeStatus($query, string $status)
    {
        if (in_array($status, self::ALLOWED_STATUSES, true)) {
            $query->where('status', $status);
        }

        return $query;
    }

    // Helpers
    public function isCancelable(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function setStatusSafe(string $status): bool
    {
        if (! in_array($status, self::ALLOWED_STATUSES, true)) {
            return false;
        }
        $this->status = $status;

        return $this->save();
    }

    public function markPaid(): bool
    {
        if ($this->payment_status === self::PAYMENT_PAID) {
            return false; // already paid; no-op
        }
        $this->payment_status = self::PAYMENT_PAID;

        return $this->save();
    }
}
