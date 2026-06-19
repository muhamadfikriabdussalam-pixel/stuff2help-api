<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingDriver extends Model
{
    protected $table = 'rating_driver';

    protected $fillable = [
        'distribusi_id',
        'driver_id',
        'pemberi_rating_id',
        'rating',
        'ulasan'
    ];

    public function distribusi()
    {
        return $this->belongsTo(Distribusi::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function pemberiRating()
    {
        return $this->belongsTo(User::class, 'pemberi_rating_id');
    }
}