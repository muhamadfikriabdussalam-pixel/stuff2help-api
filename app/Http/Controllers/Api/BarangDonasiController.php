<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBarangDonasiRequest;
use App\Http\Requests\UpdateBarangDonasiRequest;
use App\Models\BarangDonasi;
use Illuminate\Http\Request;

class BarangDonasiController extends Controller
{
    /**
     * List Barang (Donatur melihat miliknya sendiri, Admin melihat semua)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'Admin') {
            $data = BarangDonasi::with(['donatur', 'kategori'])
                ->latest()
                ->paginate(10);
        } else {
            $data = BarangDonasi::with('kategori')
                ->where('donatur_id', $user->id)
                ->latest()
                ->paginate(10);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Simpan Barang Donasi (menyertakan jumlah stok awal)
     */
    public function store(StoreBarangDonasiRequest $request)
    {
        $validated = $request->validated();

        $barang = BarangDonasi::create([
            'donatur_id' => $request->user()->id,
            'kategori_id' => $validated['kategori_id'],
            'nama_barang' => $validated['nama_barang'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'foto_url' => $validated['foto_url'] ?? null,
            'kondisi' => $validated['kondisi'],
            'status' => 'Menunggu Pencocokkan',
            'jumlah' => $validated['jumlah'],
            'jumlah_tersedia' => $validated['jumlah'],   // stok awal = total
            'jumlah_terdistribusi' => 0,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Barang berhasil ditambahkan',
            'data' => $barang
        ], 201);
    }

    /**
     * Detail Barang Donasi
     */
    public function show(BarangDonasi $barangDonasi)
    {
        return response()->json([
            'status' => 'success',
            'data' => $barangDonasi->load(['donatur', 'kategori'])
        ]);
    }

    /**
     * Update Barang Donasi (hanya pemilik donasi atau admin yang boleh)
     */
    public function update(UpdateBarangDonasiRequest $request, BarangDonasi $barangDonasi)
    {
        $user = $request->user();

        // Otorisasi: hanya donatur pemilik atau admin
        if ($user->role !== 'Admin' && $user->id !== $barangDonasi->donatur_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: Anda tidak memiliki akses ke barang ini'
            ], 403);
        }

        // Field yang boleh diupdate (jangan izinkan update jumlah, jumlah_tersedia, jumlah_terdistribusi)
        $barangDonasi->update($request->only([
            'kategori_id',
            'nama_barang',
            'deskripsi',
            'foto_url',
            'kondisi',
            'status'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Barang berhasil diperbarui',
            'data' => $barangDonasi
        ]);
    }

    /**
     * Hapus Barang Donasi (hanya pemilik donasi atau admin yang boleh)
     */
    public function destroy(Request $request, BarangDonasi $barangDonasi)
    {
        $user = $request->user();

        if ($user->role !== 'Admin' && $user->id !== $barangDonasi->donatur_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: Anda tidak memiliki akses ke barang ini'
            ], 403);
        }

        $barangDonasi->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Barang berhasil dihapus'
        ]);
    }
}