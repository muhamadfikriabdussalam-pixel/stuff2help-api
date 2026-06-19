<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List notifikasi milik user login
     */
    public function index(Request $request)
    {
        $notifications = Notifikasi::where(
            'user_id',
            $request->user()->id
        )
        ->latest()
        ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    /**
     * Detail notifikasi
     */
    public function show(
        Notifikasi $notification,
        Request $request
    )
    {
        if (
            $notification->user_id !==
            $request->user()->id
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $notification
        ]);
    }

    /**
     * Tandai sudah dibaca
     */
    public function markAsRead(
        Notifikasi $notification,
        Request $request
    )
    {
        if (
            $notification->user_id !==
            $request->user()->id
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden'
            ], 403);
        }

        $notification->update([
            'dibaca' => true,
            'dibaca_pada' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi ditandai telah dibaca',
            'data' => $notification->fresh()
        ]);
    }
}