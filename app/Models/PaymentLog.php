<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id', 'user_id', 'action', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function getLabelAttribute(): string
    {
        return match ($this->action) {
            'manual_submit' => 'Pengajuan verifikasi pembayaran manual',
            'online_paid' => 'Pembayaran online tercatat',
            'proof_uploaded' => 'Bukti pembayaran diunggah',
            'admin_approved' => 'Pembayaran disetujui admin',
            'admin_rejected' => 'Pembayaran ditolak admin',
            default => ucfirst(str_replace('_', ' ', (string) $this->action)),
        };
    }
}
