<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiwayatPoin;
use Illuminate\Http\Request;

class RewardPointController extends Controller
{
    /**
     * Daftar riwayat poin
     *
     * Admin  : melihat semua
     * Donatur: melihat miliknya sendiri
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = RiwayatPoin::with([
            'donatur',
            'distribusi'
        ])->latest();

        if ($user->role === 'Donatur') {
            $query->where('donatur_id', $user->id);
        }

        $riwayatPoin = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $riwayatPoin
        ]);
    }

    /**
     * Detail riwayat poin
     *
     * Admin  : bebas melihat
     * Donatur: hanya miliknya sendiri
     */
    public function show($id, Request $request)
    {
        $riwayatPoin = RiwayatPoin::with([
            'donatur',
            'distribusi'
        ])->findOrFail($id);

        $user = $request->user();

        if (
            $user->role === 'Donatur' &&
            $riwayatPoin->donatur_id !== $user->id
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $riwayatPoin
        ]);
    }
}