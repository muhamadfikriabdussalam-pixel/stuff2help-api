<?php

namespace App\Services;

use App\Models\Distribusi;
use App\Models\User;
use Illuminate\Support\Str;

class QrVerificationService
{
    /**
     * Generate QR Token
     */
    public function generateToken(
        Distribusi $distribusi
    ): string {

        if ($distribusi->qr_token) {
            return $distribusi->qr_token;
        }

        $token =
            'DIST-' .
            $distribusi->id .
            '-' .
            Str::random(20);

        $distribusi->update([
            'qr_token' => $token
        ]);

        return $token;
    }

    /**
     * Verify QR
     */
    public function verify(
        Distribusi $distribusi,
        User $user
    ): Distribusi {

        /*
        |--------------------------------------------------------------------------
        | Hanya Penerima
        |--------------------------------------------------------------------------
        */
        if ($user->role !== 'Penerima') {
            throw new \Exception(
                'Hanya penerima yang dapat memverifikasi QR'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Harus status Menunggu Verifikasi
        |--------------------------------------------------------------------------
        */
        if (
            $distribusi->status !== 'Menunggu Verifikasi'
        ) {
            throw new \Exception(
                'Distribusi belum siap diverifikasi'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sudah diverifikasi
        |--------------------------------------------------------------------------
        */
        if ($distribusi->qr_verified_at) {
            throw new \Exception(
                'QR sudah diverifikasi'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pastikan penerima sesuai distribusi
        |--------------------------------------------------------------------------
        */
        $distribusi->load('permintaan');

        if (
            !$distribusi->permintaan
        ) {
            throw new \Exception(
                'Data penerima tidak ditemukan'
            );
        }

        if (
            $distribusi->permintaan->penerima_id
            !==
            $user->id
        ) {
            throw new \Exception(
                'Anda bukan penerima distribusi ini'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan verifikasi
        |--------------------------------------------------------------------------
        */
        $distribusi->update([
            'verified_by' => $user->id,
            'qr_verified_at' => now()
        ]);

        return $distribusi->fresh();
    }
}