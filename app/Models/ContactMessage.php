<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'subject', 'phone', 'message', 'ip', 'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
