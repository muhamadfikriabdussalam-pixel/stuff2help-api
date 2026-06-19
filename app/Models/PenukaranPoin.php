<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenukaranPoin extends Model
{
    protected $table = 'penukaran_poin';

    protected $fillable = [
        'donatur_id',
        'voucher_id',
        'poin_digunakan',
        'status'
    ];

    public function donatur()
    {
        return $this->belongsTo(User::class, 'donatur_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}