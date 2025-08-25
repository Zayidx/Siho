<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;
   protected $fillable = [
        'full_name',
        'email',
        'phone',
        'address',
        'id_number',
        'photo',
        'date_of_birth',
    ];
    public function reservations()
    {
        return $this->hasMany(Reservations::class, );
    }

}
