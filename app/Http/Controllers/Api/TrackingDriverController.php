<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackingDriverRequest;
use App\Models\Distribusi;
use App\Models\TrackingDriver;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TrackingDriverController extends Controller
{
    /**
     * Driver memulai penjemputan
     */
    public function startPickup(StoreTrackingDriverRequest $request, NotificationService $notificationService)
    {
        $distribusi = Distribusi::findOrFail($request->distribusi_id);

        if (auth()->id() !== $distribusi->driver_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden'
            ], 403);
        }

        // ✅ Proteksi closed status (tambahkan Menunggu Verifikasi)
        $closedStatuses = ['Menunggu Verifikasi', 'Selesai', 'Dibatalkan'];
        if (in_array($distribusi->status, $closedStatuses)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Distribusi sudah ditutup'
            ], 422);
        }

        if ($distribusi->status !== 'Driver Ditugaskan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Distribusi tidak dapat memulai penjemputan'
            ], 422);
        }

        $tracking = TrackingDriver::create([
            'distribusi_id' => $distribusi->id,
            'driver_id' => auth()->id(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'waktu_lokasi' => $request->waktu_lokasi
        ]);

        $distribusi->update([
            'status' => 'Dalam Penjemputan'
        ]);

        // Notifikasi
        $distribusi->load(['barang', 'permintaan']);

        if ($distribusi->barang && $distribusi->barang->donatur) {
            $notificationService->send(
                $distribusi->barang->donatur,
                'Distribusi Dimulai',
                'Driver sedang melakukan penjemputan untuk distribusi #' . $distribusi->id
            );
        }

        if ($distribusi->permintaan && $distribusi->permintaan->penerima) {
            $notificationService->send(
                $distribusi->permintaan->penerima,
                'Distribusi Dimulai',
                'Driver sedang melakukan penjemputan untuk distribusi #' . $distribusi->id
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Penjemputan dimulai',
            'data' => $tracking
        ]);
    }

    /**
     * Driver memulai pengiriman
     */
    public function startDelivery(StoreTrackingDriverRequest $request, NotificationService $notificationService)
    {
        $distribusi = Distribusi::findOrFail($request->distribusi_id);

        if (auth()->id() !== $distribusi->driver_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden'
            ], 403);
        }

        // ✅ Proteksi closed status (tambahkan Menunggu Verifikasi)
        $closedStatuses = ['Menunggu Verifikasi', 'Selesai', 'Dibatalkan'];
        if (in_array($distribusi->status, $closedStatuses)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Distribusi sudah ditutup'
            ], 422);
        }

        if ($distribusi->status !== 'Dalam Penjemputan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Distribusi belum memasuki tahap penjemputan'
            ], 422);
        }

        $tracking = TrackingDriver::create([
            'distribusi_id' => $distribusi->id,
            'driver_id' => auth()->id(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'waktu_lokasi' => $request->waktu_lokasi
        ]);

        $distribusi->update([
            'status' => 'Dalam Pengiriman'
        ]);

        // Notifikasi
        $distribusi->load(['barang', 'permintaan']);

        if ($distribusi->barang && $distribusi->barang->donatur) {
            $notificationService->send(
                $distribusi->barang->donatur,
                'Barang Dalam Pengiriman',
                'Barang donasi sedang dikirim pada distribusi #' . $distribusi->id
            );
        }

        if ($distribusi->permintaan && $distribusi->permintaan->penerima) {
            $notificationService->send(
                $distribusi->permintaan->penerima,
                'Barang Dalam Pengiriman',
                'Barang bantuan sedang dikirim pada distribusi #' . $distribusi->id
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pengiriman dimulai',
            'data' => $tracking
        ]);
    }

    /**
     * Update lokasi driver selama perjalanan
     */
    public function updateLocation(StoreTrackingDriverRequest $request)
    {
        $distribusi = Distribusi::findOrFail($request->distribusi_id);

        if (auth()->id() !== $distribusi->driver_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden'
            ], 403);
        }

        // ✅ Hanya status yang benar-benar dalam perjalanan
        $allowedStatuses = ['Dalam Penjemputan', 'Dalam Pengiriman'];
        if (!in_array($distribusi->status, $allowedStatuses)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tracking tidak dapat dilakukan pada status ini'
            ], 422);
        }

        $tracking = TrackingDriver::create([
            'distribusi_id' => $distribusi->id,
            'driver_id' => auth()->id(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'waktu_lokasi' => $request->waktu_lokasi
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi berhasil diperbarui',
            'data' => $tracking
        ]);
    }

    /**
     * Lihat histori tracking
     */
    public function history($distribusiId, Request $request)
    {
        $distribusi = Distribusi::with([
            'barang',
            'permintaan'
        ])->findOrFail($distribusiId);

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

        $tracking = TrackingDriver::with('driver')
            ->where('distribusi_id', $distribusi->id)
            ->orderBy('waktu_lokasi', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $tracking
        ]);
    }
}