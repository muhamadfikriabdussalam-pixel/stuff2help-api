<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBuktiPengirimanRequest;
use App\Models\BuktiPengiriman;
use App\Models\Distribusi;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BuktiPengirimanController extends Controller
{
    public function store(StoreBuktiPengirimanRequest $request)
    {
        $distribusi = Distribusi::findOrFail($request->distribusi_id);

        if (auth()->id() !== $distribusi->driver_id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $closedStatuses = ['Menunggu Verifikasi', 'Selesai', 'Dibatalkan'];
        if (in_array($distribusi->status, $closedStatuses)) {
            return response()->json(['status' => 'error', 'message' => 'Distribusi sudah ditutup'], 422);
        }

        if ($distribusi->status !== 'Dalam Pengiriman') {
            return response()->json(['status' => 'error', 'message' => 'Distribusi belum dalam tahap pengiriman'], 422);
        }

        $exists = BuktiPengiriman::where('distribusi_id', $distribusi->id)->exists();
        if ($exists) {
            return response()->json(['status' => 'error', 'message' => 'Bukti pengiriman sudah ada'], 422);
        }

        $path = $request->file('foto_bukti')->store('bukti_pengiriman', 'public');

        $bukti = BuktiPengiriman::create([
            'distribusi_id' => $distribusi->id,
            'foto_bukti' => $path,
            'nama_penerima' => $request->nama_penerima,
            'catatan' => $request->catatan,
            'waktu_serah_terima' => $request->waktu_serah_terima,
            'qr_token' => Str::uuid()
        ]);

        $distribusi->update(['status' => 'Menunggu Verifikasi']);

        return response()->json([
            'status' => 'success',
            'message' => 'Bukti pengiriman berhasil disimpan. Menunggu verifikasi penerima.',
            'data' => $bukti
        ], 201);
    }

    public function show($distribusiId, Request $request)
    {
        $bukti = BuktiPengiriman::with('distribusi')
            ->where('distribusi_id', $distribusiId)
            ->firstOrFail();

        $distribusi = $bukti->distribusi->load(['barang', 'permintaan']);
        $user = $request->user();

        if ($user->role === 'Driver') {
            if ($distribusi->driver_id !== $user->id) {
                return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
            }
        }

        if ($user->role === 'Donatur') {
            if (!$distribusi->barang || $distribusi->barang->donatur_id !== $user->id) {
                return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
            }
        }

        if ($user->role === 'Penerima') {
            if (!$distribusi->permintaan || $distribusi->permintaan->penerima_id !== $user->id) {
                return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $bukti
        ]);
    }
}