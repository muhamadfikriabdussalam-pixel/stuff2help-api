<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus CHECK lama
        DB::statement("
            ALTER TABLE distribusi
            DROP CONSTRAINT distribusi_status_check
        ");

        // Tambah CHECK baru
        DB::statement("
            ALTER TABLE distribusi
            ADD CONSTRAINT distribusi_status_check
            CHECK (
                status IN (
                    'Menunggu Driver',
                    'Driver Ditugaskan',
                    'Dalam Penjemputan',
                    'Dalam Pengiriman',
                    'Terkirim',
                    'Selesai',
                    'Dibatalkan'
                )
            )
        ");

        // Konversi data lama jika ada
        DB::statement("
            UPDATE distribusi
            SET status = 'Menunggu Driver'
            WHERE status = 'Dijadwalkan'
        ");

        DB::statement("
            UPDATE distribusi
            SET status = 'Dalam Penjemputan'
            WHERE status = 'Diambil'
        ");

        DB::statement("
            UPDATE distribusi
            SET status = 'Dalam Pengiriman'
            WHERE status = 'Dikirim'
        ");
    }

    public function down(): void
    {
        // Hapus CHECK baru
        DB::statement("
            ALTER TABLE distribusi
            DROP CONSTRAINT distribusi_status_check
        ");

        // Kembalikan CHECK lama
        DB::statement("
            ALTER TABLE distribusi
            ADD CONSTRAINT distribusi_status_check
            CHECK (
                status IN (
                    'Dijadwalkan',
                    'Diambil',
                    'Dikirim',
                    'Selesai',
                    'Dibatalkan'
                )
            )
        ");

        // Rollback data
        DB::statement("
            UPDATE distribusi
            SET status = 'Dijadwalkan'
            WHERE status = 'Menunggu Driver'
        ");

        DB::statement("
            UPDATE distribusi
            SET status = 'Diambil'
            WHERE status = 'Dalam Penjemputan'
        ");

        DB::statement("
            UPDATE distribusi
            SET status = 'Dikirim'
            WHERE status = 'Dalam Pengiriman'
        ");
    }
};