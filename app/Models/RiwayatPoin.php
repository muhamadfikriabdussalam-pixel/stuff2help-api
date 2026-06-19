<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPoin extends Model
{
    protected $table = 'riwayat_poin';

    protected $fillable = [
        'donatur_id',
        'distribusi_id',
        'jumlah_poin',
        'keterangan'
    ];

    public function donatur()
    {
        return $this->belongsTo(User::class, 'donatur_id');
    }

    public function distribusi()
    {
        return $this->belongsTo(Distribusi::class, 'distribusi_id');
    }
}