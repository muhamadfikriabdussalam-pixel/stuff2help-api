<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;

class NotificationService
{
    public function send(
        User $user,
        string $judul,
        string $pesan
    ): Notifikasi {
        return Notifikasi::create([
            'user_id' => $user->id,
            'judul' => $judul,
            'pesan' => $pesan,
            'dibaca' => false
        ]);
    }
}