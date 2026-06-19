<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRatingDriverRequest;
use App\Models\Distribusi;
use App\Models\RatingDriver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\RewardPointService; // Tambahkan import

class RatingDriverController extends Controller
{
    /**
     * Penerima memberi rating driver
     */
    public function store(StoreRatingDriverRequest $request, RewardPointService $rewardService) // Tambah dependency injection
    {
        $distribusi = Distribusi::with('permintaan')
            ->findOrFail($request->distribusi_id);

        /*
        |--------------------------------------------------------------------------
        | Rule 1
        | Hanya penerima terkait
        |--------------------------------------------------------------------------
        */
        if (
            !$distribusi->permintaan ||
            auth()->id() !== $distribusi->permintaan->penerima_id
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Rule 2
        | Harus status Terkirim
        |--------------------------------------------------------------------------
        */
        if ($distribusi->status !== 'Terkirim' && $distribusi->status !== 'Selesai') {
            return response()->json([
                'status' => 'error',
                'message' => 'Distribusi belum selesai dikirim'
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Rule 3
        | Tidak boleh rating dua kali
        |--------------------------------------------------------------------------
        */
        $exists = RatingDriver::where(
            'distribusi_id',
            $distribusi->id
        )->where(
            'pemberi_rating_id',
            auth()->id()
        )->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rating sudah diberikan'
            ], 422);
        }

        DB::beginTransaction();

        try {

            $rating = RatingDriver::create([
                'distribusi_id' => $distribusi->id,
                'driver_id' => $distribusi->driver_id,
                'pemberi_rating_id' => auth()->id(),
                'rating' => $request->rating,
                'ulasan' => $request->ulasan
            ]);

            /*
            |--------------------------------------------------------------------------
            | Rule 4
            | Distribusi otomatis menjadi Selesai
            |--------------------------------------------------------------------------
            */
            $distribusi->update([
                'status' => 'Selesai'
            ]);

            // Panggil service untuk menambah poin reward ke donatur
            $rewardService->generateFromDistribusi($distribusi->fresh());

            /*
            |--------------------------------------------------------------------------
            | Rule 5
            | Update rata-rata rating driver
            |--------------------------------------------------------------------------
            */
            $averageRating = RatingDriver::where(
                'driver_id',
                $distribusi->driver_id
            )->avg('rating');

            User::where(
                'id',
                $distribusi->driver_id
            )->update([
                'rating' => round($averageRating, 2)
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Rating berhasil diberikan',
                'data' => $rating
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memberikan rating'
            ], 500);
        }
    }

    /**
     * Lihat rating berdasarkan distribusi
     */
    public function show($distribusiId, Request $request)
    {
        $rating = RatingDriver::with([
            'driver',
            'pemberiRating',
            'distribusi'
        ])
        ->where('distribusi_id', $distribusiId)
        ->firstOrFail();

        $distribusi = $rating->distribusi->load([
            'barang',
            'permintaan'
        ]);

        $user = $request->user();

        if ($user->role === 'Driver') {
            if ($distribusi->driver_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden'
                ], 403);
            }
        }

        if ($user->role === 'Donatur') {
            if (
                !$distribusi->barang ||
                $distribusi->barang->donatur_id !== $user->id
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden'
                ], 403);
            }
        }

        if ($user->role === 'Penerima') {
            if (
                !$distribusi->permintaan ||
                $distribusi->permintaan->penerima_id !== $user->id
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden'
                ], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $rating
        ]);
    }
}