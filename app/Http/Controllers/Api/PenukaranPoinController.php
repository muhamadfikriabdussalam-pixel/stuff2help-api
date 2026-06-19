<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenukaranPoin;
use Illuminate\Http\Request;

class PenukaranPoinController extends Controller
{
    /**
     * Daftar penukaran voucher (Donatur: milik sendiri, Admin: semua)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = PenukaranPoin::with(['voucher', 'donatur'])->latest();

        if ($user->role === 'Donatur') {
            $query->where('donatur_id', $user->id);
        }

        $data = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Detail penukaran
     */
    public function show($id, Request $request)
    {
        $penukaran = PenukaranPoin::with(['voucher', 'donatur'])->findOrFail($id);

        $user = $request->user();
        if ($user->role === 'Donatur' && $penukaran->donatur_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $penukaran
        ]);
    }
}