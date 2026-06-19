<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermintaanBarangRequest;
use App\Http\Requests\UpdatePermintaanBarangRequest;
use App\Models\PermintaanBarang;
use Illuminate\Http\Request;

class PermintaanBarangController extends Controller
{
    /**
     * List semua permintaan aktif
     */
    public function index()
    {
        $data = PermintaanBarang::with([
            'penerima',
            'kategori'
        ])
        ->where('status', 'Aktif')
        ->latest()
        ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Simpan permintaan baru
     */
    public function store(StorePermintaanBarangRequest $request)
    {
        $validated = $request->validated();

        $permintaan = PermintaanBarang::create([
            'penerima_id' => $request->user()->id,
            'kategori_id' => $validated['kategori_id'],
            'judul_permintaan' => $validated['judul_permintaan'],
            'jumlah' => $validated['jumlah'],
            'jumlah_terpenuhi' => 0,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'prioritas' => $validated['prioritas'],
            'status' => 'Aktif',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Permintaan berhasil dibuat',
            'data' => $permintaan
        ], 201);
    }

    /**
     * Detail permintaan
     */
    public function show(PermintaanBarang $permintaanBarang)
    {
        return response()->json([
            'status' => 'success',
            'data' => $permintaanBarang->load([
                'penerima',
                'kategori'
            ])
        ]);
    }

    /**
     * Update permintaan
     */
    public function update(
        UpdatePermintaanBarangRequest $request,
        PermintaanBarang $permintaanBarang
    ) {
        $user = $request->user();

        if (
            $user->role !== 'Admin'
            &&
            $user->id !== $permintaanBarang->penerima_id
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: Anda tidak memiliki akses ke permintaan ini'
            ], 403);
        }

        $permintaanBarang->update(
            $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Permintaan berhasil diperbarui',
            'data' => $permintaanBarang
        ]);
    }

    /**
     * Hapus permintaan
     */
    public function destroy(
        Request $request,
        PermintaanBarang $permintaanBarang
    ) {
        $user = $request->user();

        if (
            $user->role !== 'Admin'
            &&
            $user->id !== $permintaanBarang->penerima_id
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: Anda tidak memiliki akses ke permintaan ini'
            ], 403);
        }

        $permintaanBarang->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Permintaan berhasil dihapus'
        ]);
    }
}