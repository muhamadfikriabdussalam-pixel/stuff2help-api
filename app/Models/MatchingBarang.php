<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchingBarang extends Model
{
    protected $table = 'matching_barang';

    protected $fillable = [
        'barang_id',
        'permintaan_id',
        'skor_kecocokan',
        'jumlah_rekomendasi',
        'status'
    ];

    public function barang()
    {
        return $this->belongsTo(BarangDonasi::class, 'barang_id');
    }

    public function permintaan()
    {
        return $this->belongsTo(PermintaanBarang::class, 'permintaan_id');
    }
}