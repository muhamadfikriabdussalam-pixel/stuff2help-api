<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchingBarang;
use App\Services\SmartMatchingService;
use Illuminate\Support\Facades\DB;

class MatchingBarangController extends Controller
{
    protected $smartMatchingService;

    public function __construct(
        SmartMatchingService $smartMatchingService
    ) {
        $this->smartMatchingService = $smartMatchingService;
    }

    /**
     * Generate Matching
     */
    public function generate()
    {
        $result = $this->smartMatchingService->generate();

        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Matching berhasil dibuat',
            'total_generated' => $result['total_generated']
        ]);
    }

    /**
     * List Matching
     */
    public function index()
    {
        $data = MatchingBarang::with([
            'barang',
            'permintaan'
        ])
        ->latest()
        ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Detail Matching
     */
    public function show(MatchingBarang $matchingBarang)
    {
        return response()->json([
            'status' => 'success',
            'data' => $matchingBarang->load([
                'barang',
                'permintaan'
            ])
        ]);
    }

    /**
     * Approve Matching
     */
    public function approve(
        MatchingBarang $matchingBarang
    ) {

        DB::beginTransaction();

        try {

            $matchingBarang->update([
                'status' => 'Disetujui'
            ]);

            $barang = $matchingBarang->barang;

            $barang->update([
                'status' => 'Tercocokkan'
            ]);

            $permintaan = $matchingBarang->permintaan;

            $permintaan->jumlah_terpenuhi +=
                $matchingBarang->jumlah_rekomendasi;

            if (
                $permintaan->jumlah_terpenuhi
                >=
                $permintaan->jumlah
            ) {

                $permintaan->status = 'Terpenuhi';

            } else {

                $permintaan->status = 'Aktif';
            }

            $permintaan->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Matching disetujui',
                'data' => $matchingBarang->fresh()
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject Matching
     */
    public function reject(
        MatchingBarang $matchingBarang
    ) {

        $matchingBarang->update([
            'status' => 'Ditolak'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Matching ditolak',
            'data' => $matchingBarang
        ]);
    }
}