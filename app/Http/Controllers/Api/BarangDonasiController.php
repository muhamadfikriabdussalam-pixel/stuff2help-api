<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBarangDonasiRequest;
use App\Http\Requests\UpdateBarangDonasiRequest;
use App\Models\BarangDonasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BarangDonasiController extends Controller
{
    /**
     * List Barang (Donatur melihat miliknya sendiri, Admin melihat semua)
     * Mendukung filter bulan & tahun (opsional) via query parameter month & year.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Query dasar dengan relasi
        $query = BarangDonasi::with(['donatur', 'kategori', 'distribusi']);

        // Filter berdasarkan bulan & tahun (opsional)
        if ($request->has('month') && $request->has('year')) {
            $month = (int) $request->input('month');
            $year  = (int) $request->input('year');
            $query->whereMonth('created_at', $month)
                  ->whereYear('created_at', $year);
        }

        // Role-based filtering
        if ($user->role === 'Admin') {
            // Admin melihat semua, tidak perlu tambahan where
        } else {
            $query->where('donatur_id', $user->id);
        }

        $data = $query->latest()->paginate(10);

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

        $fotoUrl = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('barang', $filename, 'public');
            $fotoUrl = Storage::url($path);
        } else {
            $fotoUrl = $validated['foto_url'] ?? null;
        }

        $barang = BarangDonasi::create([
            'donatur_id' => $request->user()->id,
            'kategori_id' => $validated['kategori_id'],
            'nama_barang' => $validated['nama_barang'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'foto_url' => $fotoUrl,
            'kondisi' => $validated['kondisi'],
            'status' => 'Menunggu Pencocokkan',
            'jumlah' => $validated['jumlah'],
            'jumlah_tersedia' => $validated['jumlah'],
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
            'data' => $barangDonasi->load(['donatur', 'kategori', 'distribusi'])
        ]);
    }

    /**
     * Update Barang Donasi (hanya pemilik donasi atau admin yang boleh)
     */
    public function update(UpdateBarangDonasiRequest $request, BarangDonasi $barangDonasi)
    {
        $user = $request->user();

        if ($user->role !== 'Admin' && $user->id !== $barangDonasi->donatur_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: Anda tidak memiliki akses ke barang ini'
            ], 403);
        }

        $fotoUrl = $request->foto_url;
        if ($request->hasFile('foto')) {
            if ($barangDonasi->foto_url) {
                $oldPath = str_replace('/storage/', '', $barangDonasi->foto_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $file = $request->file('foto');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('barang', $filename, 'public');
            $fotoUrl = Storage::url($path);
        }

        $barangDonasi->update([
            'kategori_id' => $request->kategori_id ?? $barangDonasi->kategori_id,
            'nama_barang' => $request->nama_barang ?? $barangDonasi->nama_barang,
            'deskripsi' => $request->deskripsi ?? $barangDonasi->deskripsi,
            'foto_url' => $fotoUrl,
            'kondisi' => $request->kondisi ?? $barangDonasi->kondisi,
            'status' => $request->status ?? $barangDonasi->status,
        ]);

        $barangDonasi->load(['donatur', 'kategori', 'distribusi']);

        return response()->json([
            'status' => 'success',
            'message' => 'Barang berhasil diperbarui',
            'data' => $barangDonasi
        ]);
    }

    /**
     * Hapus Barang Donasi
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

        if ($barangDonasi->foto_url) {
            $path = str_replace('/storage/', '', $barangDonasi->foto_url);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $barangDonasi->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Barang berhasil dihapus'
        ]);
    }
}