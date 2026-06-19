<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BarangDonasi;
use App\Models\PermintaanBarang;
use App\Models\MatchingBarang;
use App\Models\Distribusi;
use App\Models\Voucher;
use App\Models\PenukaranPoin;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard Admin
     */
    public function admin()
    {
        if (auth()->user()->role !== 'Admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden Access'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_user' => User::count(),
                'total_donatur' => User::where('role', 'Donatur')->count(),
                'total_penerima' => User::where('role', 'Penerima')->count(),
                'total_driver' => User::where('role', 'Driver')->count(),
                'total_barang' => BarangDonasi::count(),
                'total_permintaan' => PermintaanBarang::count(),
                'total_matching' => MatchingBarang::count(),
                'total_distribusi' => Distribusi::count(),
                'total_poin' => User::sum('poin'),
                'total_voucher' => Voucher::count(),
                'total_penukaran' => PenukaranPoin::count()
            ]
        ]);
    }

    /**
     * Dashboard Donatur
     */
    public function donatur()
    {
        if (auth()->user()->role !== 'Donatur') {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden Access'
            ], 403);
        }

        $userId = auth()->id();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_barang' => BarangDonasi::where('donatur_id', $userId)->count(),
                'total_donasi' => BarangDonasi::where('donatur_id', $userId)->sum('jumlah'),
                'total_distribusi' => Distribusi::whereHas('barang', function ($q) use ($userId) {
                    $q->where('donatur_id', $userId);
                })->count(),
                'total_poin' => auth()->user()->poin,
                'voucher_ditukar' => PenukaranPoin::where('donatur_id', $userId)->count(),
            ]
        ]);
    }

    /**
     * Dashboard Driver
     */
    public function driver()
    {
        if (auth()->user()->role !== 'Driver') {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden Access'
            ], 403);
        }

        $userId = auth()->id();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_distribusi' => Distribusi::where('driver_id', $userId)->count(),
                'distribusi_aktif' => Distribusi::where('driver_id', $userId)
                    ->whereIn('status', [
                        'Driver Ditugaskan',
                        'Dalam Penjemputan',
                        'Dalam Pengiriman',
                        'Terkirim'
                    ])
                    ->count(),
                'distribusi_selesai' => Distribusi::where('driver_id', $userId)
                    ->where('status', 'Selesai')
                    ->count(),
                'rating_rata_rata' => auth()->user()->rating
            ]
        ]);
    }

    /**
     * Dashboard Penerima
     */
    public function dashboardPenerima(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'Penerima') {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden Access'
            ], 403);
        }

        $totalPermintaan = PermintaanBarang::where('penerima_id', $user->id)->count();

        $permintaanTerpenuhi = PermintaanBarang::where('penerima_id', $user->id)
            ->where('status', 'Terpenuhi')
            ->count();

        $riwayatDistribusi = Distribusi::whereHas('permintaan', function ($query) use ($user) {
            $query->where('penerima_id', $user->id);
        })->count();

        $totalBarangDiterima = Distribusi::whereHas('permintaan', function ($query) use ($user) {
            $query->where('penerima_id', $user->id);
        })->sum('jumlah_disalurkan');

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_permintaan' => $totalPermintaan,
                'permintaan_terpenuhi' => $permintaanTerpenuhi,
                'riwayat_distribusi' => $riwayatDistribusi,
                'total_barang_diterima' => $totalBarangDiterima
            ]
        ]);
    }
}