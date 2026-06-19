<?php

namespace App\Services;

use App\Models\BarangDonasi;
use App\Models\PermintaanBarang;
use App\Models\MatchingBarang;
use Illuminate\Support\Facades\DB;

class SmartMatchingService
{
    public function generate()
    {
        DB::beginTransaction();

        try {

            $totalGenerated = 0;

            $barangList = BarangDonasi::where(
                    'status',
                    'Menunggu Pencocokkan'
                )
                ->where('jumlah_tersedia', '>', 0)
                ->get();

            $permintaanList = PermintaanBarang::where(
                    'status',
                    'Aktif'
                )
                ->get();

            foreach ($barangList as $barang) {

                foreach ($permintaanList as $permintaan) {

                    /*
                    |--------------------------------------------------------------------------
                    | Kategori wajib sama
                    |--------------------------------------------------------------------------
                    */
                    if (
                        $barang->kategori_id
                        !=
                        $permintaan->kategori_id
                    ) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Hindari duplicate matching
                    |--------------------------------------------------------------------------
                    */
                    $exists = MatchingBarang::where(
                            'barang_id',
                            $barang->id
                        )
                        ->where(
                            'permintaan_id',
                            $permintaan->id
                        )
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Hitung skor kecocokan
                    |--------------------------------------------------------------------------
                    */
                    $score = min(
                        (
                            $barang->jumlah_tersedia
                            /
                            $permintaan->jumlah
                        ) * 100,
                        100
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Threshold minimal
                    |--------------------------------------------------------------------------
                    */
                    if ($score < 50) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Hitung jumlah rekomendasi
                    |--------------------------------------------------------------------------
                    */
                    $sisaKebutuhan =
                        $permintaan->jumlah
                        -
                        $permintaan->jumlah_terpenuhi;

                    if ($sisaKebutuhan <= 0) {
                        continue;
                    }

                    $jumlahRekomendasi = min(
                        $barang->jumlah_tersedia,
                        $sisaKebutuhan
                    );

                    MatchingBarang::create([
                        'barang_id' => $barang->id,
                        'permintaan_id' => $permintaan->id,
                        'skor_kecocokan' => round($score, 2),
                        'jumlah_rekomendasi' => $jumlahRekomendasi,
                        'status' => 'Direkomendasikan'
                    ]);

                    $totalGenerated++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'total_generated' => $totalGenerated
            ];

        } catch (\Exception $e) {

            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}