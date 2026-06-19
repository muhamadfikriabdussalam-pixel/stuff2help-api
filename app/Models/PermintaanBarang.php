<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanBarang extends Model
{
    protected $table = 'permintaan_barang';

    protected $fillable = [
        'penerima_id',
        'kategori_id',
        'judul_permintaan',
        'jumlah',
        'jumlah_terpenuhi',
        'deskripsi',
        'prioritas',
        'status'
    ];

    public function penerima()
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriBarang::class, 'kategori_id');
    }

    public function matching()
    {
        return $this->hasMany(MatchingBarang::class, 'permintaan_id');
    }
}