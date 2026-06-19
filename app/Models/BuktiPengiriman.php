<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // ✅ TAMBAHKAN INI

class BuktiPengiriman extends Model
{
    protected $table = 'bukti_pengiriman';

    protected $fillable = [
        'distribusi_id',
        'foto_bukti',
        'nama_penerima',
        'catatan',
        'waktu_serah_terima',
        'qr_token',
    ];

    // ✅ TAMBAHKAN: Agar response API memiliki field 'foto_url'
    protected $appends = [
        'foto_url'
    ];

    public function distribusi()
    {
        return $this->belongsTo(Distribusi::class);
    }

    // ✅ TAMBAHKAN: Accessor untuk generate URL lengkap
    public function getFotoUrlAttribute()
    {
        if (!$this->foto_bukti) {
            return null;
        }

        return Storage::url($this->foto_bukti);
    }
}