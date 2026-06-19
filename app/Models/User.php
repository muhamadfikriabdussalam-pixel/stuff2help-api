<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nama',
        'username',
        'email',
        'password',
        'role',
        'no_hp',
        'alamat',
        'kota',
        'foto_profil',
        'is_verified',
        'poin',
        'rating',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_verified' => 'boolean',
    ];

    // ========== RELATIONS ==========
    public function barangDonasi()
    {
        return $this->hasMany(BarangDonasi::class, 'donatur_id');
    }

    public function permintaanBarang()
    {
        return $this->hasMany(PermintaanBarang::class, 'penerima_id');
    }

    public function distribusiDriver()
    {
        return $this->hasMany(Distribusi::class, 'driver_id');
    }

    public function distribusiAdmin()
    {
        return $this->hasMany(Distribusi::class, 'admin_id');
    }

    public function riwayatPoin()
    {
        return $this->hasMany(RiwayatPoin::class, 'donatur_id');
    }

    public function penukaranPoin()
    {
        return $this->hasMany(PenukaranPoin::class, 'donatur_id');
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class, 'user_id');
    }
}