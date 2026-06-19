<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Distribusi;
use App\Services\QrVerificationService;
use Illuminate\Http\Request;

class QrVerificationController extends Controller
{
    /**
     * Generate QR Token (Admin only)
     */
    public function generate(Distribusi $distribusi, QrVerificationService $service)
    {
        if (auth()->user()->role !== 'Admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden Access'
            ], 403);
        }

        $token = $service->generateToken($distribusi);

        return response()->json([
            'status' => 'success',
            'message' => 'QR token berhasil dibuat',
            'data' => [
                'distribusi_id' => $distribusi->id,
                'qr_token' => $token
            ]
        ]);
    }

    /**
     * Verify QR (Penerima only)
     */
    public function verify(Request $request, QrVerificationService $service)
    {
        $request->validate([
            'qr_token' => ['required', 'string']
        ]);

        $distribusi = Distribusi::where('qr_token', $request->qr_token)->first();

        if (!$distribusi) {
            return response()->json([
                'status' => 'error',
                'message' => 'QR tidak valid'
            ], 404);
        }

        try {
            // Verifikasi QR (update verified_by & qr_verified_at)
            $service->verify($distribusi, auth()->user());

            // ✅ PERUBAHAN UTAMA: status menjadi Selesai
            $distribusi->update([
                'status' => 'Selesai'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'QR berhasil diverifikasi. Distribusi selesai.',
                'data' => $distribusi->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Detail QR Verification
     */
    public function show(Distribusi $distribusi)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'distribusi_id' => $distribusi->id,
                'qr_token' => $distribusi->qr_token,
                'verified_by' => $distribusi->verified_by,
                'qr_verified_at' => $distribusi->qr_verified_at
            ]
        ]);
    }
}