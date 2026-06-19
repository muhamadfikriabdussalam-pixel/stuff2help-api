<?php

namespace App\Services;

use App\Models\Distribusi;
use App\Models\RiwayatPoin;
use App\Models\User;
use App\Services\NotificationService; // import
use Illuminate\Support\Facades\DB;

class RewardPointService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function generateFromDistribusi(Distribusi $distribusi)
    {
        /*
        |--------------------------------------------------------------------------
        | Rule 1
        | Distribusi harus selesai
        |--------------------------------------------------------------------------
        */
        if ($distribusi->status !== 'Selesai') {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Rule 2
        | Barang harus valid
        |--------------------------------------------------------------------------
        */
        $distribusi->load('barang');

        if (!$distribusi->barang) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Rule 3
        | Tidak boleh generate dua kali
        |--------------------------------------------------------------------------
        */
        $exists = RiwayatPoin::where('distribusi_id', $distribusi->id)->exists();

        if ($exists) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Rule 4
        | Hanya donatur mendapat poin
        |--------------------------------------------------------------------------
        */
        $donaturId = $distribusi->barang->donatur_id;

        /*
        |--------------------------------------------------------------------------
        | Rule 5
        | 1 barang = 1 poin
        |--------------------------------------------------------------------------
        */
        $poin = $distribusi->jumlah_disalurkan;

        DB::beginTransaction();

        try {
            $riwayat = RiwayatPoin::create([
                'donatur_id' => $donaturId,
                'distribusi_id' => $distribusi->id,
                'jumlah_poin' => $poin,
                'keterangan' => 'Reward poin distribusi #' . $distribusi->id
            ]);

            User::where('id', $donaturId)->increment('poin', $poin);

            // ========== KIRIM NOTIFIKASI KE DONATUR ==========
            $donatur = User::find($donaturId);
            if ($donatur) {
                $this->notificationService->send(
                    $donatur,
                    'Reward Poin',
                    'Anda mendapatkan ' . $poin . ' poin dari distribusi #' . $distribusi->id
                );
            }

            DB::commit();

            return $riwayat;
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }
}