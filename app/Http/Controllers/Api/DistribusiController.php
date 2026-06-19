<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDistribusiRequest;
use App\Http\Requests\UpdateDistribusiRequest;
use App\Models\Distribusi;
use App\Models\MatchingBarang;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DistribusiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Distribusi::with([
            'barang',
            'permintaan',
            'driver',
            'admin'
        ]);

        if ($user->role === 'Driver') {
            $query->where('driver_id', $user->id);
        } elseif ($user->role === 'Donatur') {
            $query->whereHas('barang', function ($q) use ($user) {
                $q->where('donatur_id', $user->id);
            });
        } elseif ($user->role === 'Penerima') {
            $query->whereHas('permintaan', function ($q) use ($user) {
                $q->where('penerima_id', $user->id);
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->latest()->paginate(10)
        ]);
    }

    public function store(StoreDistribusiRequest $request, NotificationService $notificationService)
    {
        $matching = MatchingBarang::with([
            'barang',
            'permintaan'
        ])->findOrFail($request->matching_id);

        if ($matching->status !== 'Disetujui') {
            return response()->json([
                'status' => 'error',
                'message' => 'Matching belum disetujui'
            ], 422);
        }

        $exists = Distribusi::where('barang_id', $matching->barang_id)
            ->where('permintaan_id', $matching->permintaan_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Distribusi sudah dibuat'
            ], 422);
        }

        if ($request->filled('driver_id')) {
            $driver = User::where('id', $request->driver_id)
                ->where('role', 'Driver')
                ->first();

            if (!$driver) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User bukan Driver'
                ], 422);
            }
        }

        $distribusi = Distribusi::create([
            'barang_id' => $matching->barang_id,
            'permintaan_id' => $matching->permintaan_id,
            'driver_id' => $request->driver_id,
            'admin_id' => auth()->id(),
            'tanggal_pickup' => $request->tanggal_pickup,
            'tanggal_pengiriman' => $request->tanggal_pengiriman,
            'jumlah_disalurkan' => $request->jumlah_disalurkan,
            'status' => $request->driver_id
                ? 'Driver Ditugaskan'
                : 'Menunggu Driver',
            'catatan' => $request->catatan
        ]);

        // Notifikasi...
        $matching->loadMissing(['barang.donatur', 'permintaan.penerima']);

        if ($distribusi->driver_id) {
            $driverUser = User::find($distribusi->driver_id);
            if ($driverUser) {
                $notificationService->send(
                    $driverUser,
                    'Distribusi Baru',
                    'Anda ditugaskan pada distribusi #' . $distribusi->id
                );
            }
        }

        if ($matching->barang && $matching->barang->donatur) {
            $notificationService->send(
                $matching->barang->donatur,
                'Distribusi Dibuat',
                'Barang donasi Anda masuk distribusi #' . $distribusi->id
            );
        }

        if ($matching->permintaan && $matching->permintaan->penerima) {
            $notificationService->send(
                $matching->permintaan->penerima,
                'Distribusi Dibuat',
                'Permintaan Anda akan segera diproses melalui distribusi #' . $distribusi->id
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Distribusi berhasil dibuat',
            'data' => $distribusi
        ], 201);
    }

    public function show(Distribusi $distribusi, Request $request)
    {
        $distribusi->load(['barang', 'permintaan', 'driver', 'admin']);

        $user = $request->user();

        if ($user->role === 'Driver') {
            if ($distribusi->driver_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden'
                ], 403);
            }
        }

        if ($user->role === 'Donatur') {
            if (!$distribusi->barang || $distribusi->barang->donatur_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden'
                ], 403);
            }
        }

        if ($user->role === 'Penerima') {
            if (!$distribusi->permintaan || $distribusi->permintaan->penerima_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden'
                ], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $distribusi
        ]);
    }

    public function update(UpdateDistribusiRequest $request, Distribusi $distribusi)
    {
        if (auth()->user()->role !== 'Admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden Access'
            ], 403);
        }

        // ✅ Proteksi closed status
        $closedStatuses = ['Menunggu Verifikasi', 'Selesai', 'Dibatalkan'];
        if (in_array($distribusi->status, $closedStatuses)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Distribusi sudah ditutup, tidak dapat diubah'
            ], 422);
        }

        $data = $request->validated();

        $statusesRequiringDriver = [
            'Driver Ditugaskan',
            'Dalam Penjemputan',
            'Dalam Pengiriman',
            'Terkirim',
            'Selesai'
        ];

        if (isset($data['status']) && in_array($data['status'], $statusesRequiringDriver)) {
            $driverId = $data['driver_id'] ?? $distribusi->driver_id;
            if (!$driverId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status ' . $data['status'] . ' membutuhkan driver_id yang valid'
                ], 422);
            }
            $driver = User::where('id', $driverId)->where('role', 'Driver')->first();
            if (!$driver) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'driver_id harus merujuk ke user dengan role Driver'
                ], 422);
            }
            $data['driver_id'] = $driverId;
        }

        if (isset($data['driver_id']) && !isset($data['status']) && $distribusi->status === 'Menunggu Driver') {
            $data['status'] = 'Driver Ditugaskan';
        }

        $distribusi->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Distribusi berhasil diperbarui',
            'data' => $distribusi
        ]);
    }

    public function destroy(Distribusi $distribusi)
    {
        if (auth()->user()->role !== 'Admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden Access'
            ], 403);
        }

        // ✅ Proteksi closed status
        $closedStatuses = ['Menunggu Verifikasi', 'Selesai', 'Dibatalkan'];
        if (in_array($distribusi->status, $closedStatuses)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Distribusi sudah ditutup, tidak dapat dihapus'
            ], 422);
        }

        $distribusi->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Distribusi berhasil dihapus'
        ]);
    }
}