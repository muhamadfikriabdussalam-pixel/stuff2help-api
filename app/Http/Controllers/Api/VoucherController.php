<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoucherRequest;
use App\Models\Voucher;
use App\Models\PenukaranPoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    /**
     * Daftar semua voucher (Admin: semua, Public: hanya aktif)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user && $user->role === 'Admin') {
            // Admin melihat semua voucher
            $voucher = Voucher::orderBy('created_at', 'desc')->paginate(10);
        } else {
            // User lain hanya melihat yang aktif
            $voucher = Voucher::where('status', 'Aktif')
                ->orderBy('poin_dibutuhkan')
                ->paginate(10);
        }

        return response()->json([
            'status' => 'success',
            'data' => $voucher
        ]);
    }

    /**
     * Detail voucher
     */
    public function show($id)
    {
        $voucher = Voucher::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $voucher
        ]);
    }

    /**
     * ✅ CREATE voucher (Admin only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_voucher' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'poin_dibutuhkan' => 'required|integer|min:1',
            'stok' => 'required|integer|min:0',
            'status' => 'sometimes|in:Aktif,Nonaktif'
        ]);

        $voucher = Voucher::create([
            'nama_voucher' => $request->nama_voucher,
            'deskripsi' => $request->deskripsi,
            'poin_dibutuhkan' => $request->poin_dibutuhkan,
            'stok' => $request->stok,
            'status' => $request->status ?? 'Aktif'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher berhasil dibuat',
            'data' => $voucher
        ], 201);
    }

    /**
     * ✅ UPDATE voucher (Admin only)
     */
    public function update(Request $request, $id)
    {
        $voucher = Voucher::findOrFail($id);

        $request->validate([
            'nama_voucher' => 'sometimes|string|max:100',
            'deskripsi' => 'nullable|string',
            'poin_dibutuhkan' => 'sometimes|integer|min:1',
            'stok' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:Aktif,Nonaktif'
        ]);

        $voucher->update($request->only([
            'nama_voucher',
            'deskripsi',
            'poin_dibutuhkan',
            'stok',
            'status'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher berhasil diperbarui',
            'data' => $voucher
        ]);
    }

    /**
     * ✅ DELETE voucher (Admin only)
     */
    public function destroy($id)
    {
        $voucher = Voucher::findOrFail($id);

        // Cek apakah ada penukaran yang terkait
        $hasPenukaran = PenukaranPoin::where('voucher_id', $id)->exists();
        if ($hasPenukaran) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher tidak dapat dihapus karena sudah ada penukaran'
            ], 422);
        }

        $voucher->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher berhasil dihapus'
        ]);
    }

    /**
     * Redeem voucher (tukar poin) – Donatur only
     */
    public function redeem(StoreVoucherRequest $request)
    {
        $user = $request->user();

        if ($user->role !== 'Donatur') {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden'
            ], 403);
        }

        $voucher = Voucher::findOrFail($request->voucher_id);

        if ($voucher->status !== 'Aktif') {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher tidak aktif'
            ], 422);
        }

        if ($voucher->stok <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stok voucher habis'
            ], 422);
        }

        if ($user->poin < $voucher->poin_dibutuhkan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Poin tidak mencukupi'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $penukaran = PenukaranPoin::create([
                'donatur_id' => $user->id,
                'voucher_id' => $voucher->id,
                'poin_digunakan' => $voucher->poin_dibutuhkan,
                'status' => 'Disetujui'
            ]);

            $user->decrement('poin', $voucher->poin_dibutuhkan);
            $voucher->decrement('stok');

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Voucher berhasil ditukarkan',
                'data' => $penukaran->load(['voucher', 'donatur'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menukarkan voucher'
            ], 500);
        }
    }
}