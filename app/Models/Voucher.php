<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $table = 'voucher';

    protected $fillable = [
        'nama_voucher',
        'deskripsi',
        'poin_dibutuhkan',
        'stok',
        'status'
    ];

    public function penukaranPoin()
    {
        return $this->hasMany(PenukaranPoin::class);
    }
}