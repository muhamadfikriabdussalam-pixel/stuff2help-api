<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriBarang extends Model
{
    protected $table = 'kategori_barang';

    protected $fillable = [
        'nama_kategori',
        'deskripsi'
    ];

    public function barangDonasi()
    {
        return $this->hasMany(BarangDonasi::class, 'kategori_id');
    }

    public function permintaanBarang()
    {
        return $this->hasMany(PermintaanBarang::class, 'kategori_id');
    }
}