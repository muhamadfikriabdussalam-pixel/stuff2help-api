<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangDonasi extends Model
{
    protected $table = 'barang_donasi';

    protected $fillable = [
        'donatur_id',
        'kategori_id',
        'nama_barang',
        'deskripsi',
        'foto_url',
        'kondisi',
        'status',

        'jumlah',
        'jumlah_tersedia',
        'jumlah_terdistribusi'
    ];

    public function donatur()
    {
        return $this->belongsTo(User::class, 'donatur_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriBarang::class, 'kategori_id');
    }

    public function matching()
    {
        return $this->hasMany(MatchingBarang::class, 'barang_id');
    }

    public function distribusi()
    {
        return $this->hasMany(Distribusi::class, 'barang_id');
    }
}