<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Reservations;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'foto',
        'role_id',
        // Tambahan agar bisa menggantikan Guest
        'full_name',
        'phone',
        'address',
        'id_number',
        'date_of_birth',
    ];

    // Otomatis sertakan accessor saat diserialisasi (JSON)
    protected $appends = [
        'foto_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservations::class, 'guest_id');
    }

    // Accessor URL foto tersimpan di disk public
    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) {
            return null;
        }
        // Pastikan file ada di disk public; jika tidak, kembalikan null agar view pakai placeholder
        if (!Storage::disk('public')->exists($this->foto)) {
            return null;
        }
        return Storage::url($this->foto);
    }
}
