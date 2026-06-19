<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distribusi extends Model
{
    protected $table = 'distribusi';

    protected $fillable = [
        'barang_id',
        'permintaan_id',
        'driver_id',
        'admin_id',
        'verified_by',
        'qr_token',
        'tanggal_pickup',
        'tanggal_pengiriman',
        'jumlah_disalurkan',
        'status',
        'catatan',
        'qr_verified_at'
    ];

    public function barang()
    {
        return $this->belongsTo(BarangDonasi::class);
    }

    public function permintaan()
    {
        return $this->belongsTo(PermintaanBarang::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function buktiPengiriman()
    {
        return $this->hasOne(BuktiPengiriman::class);
    }

    public function tracking()
    {
        return $this->hasMany(TrackingDriver::class);
    }

    public function rating()
    {
        return $this->hasOne(RatingDriver::class);
    }
}