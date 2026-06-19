<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingDriver extends Model
{
    protected $table = 'tracking_driver';

    protected $fillable = [
        'distribusi_id',
        'driver_id',
        'latitude',
        'longitude',
        'waktu_lokasi'
    ];

    public function distribusi()
    {
        return $this->belongsTo(Distribusi::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}