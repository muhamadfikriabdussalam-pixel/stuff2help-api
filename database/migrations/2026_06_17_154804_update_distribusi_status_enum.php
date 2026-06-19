<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL: ubah status menggunakan CHECK constraint
        DB::statement('ALTER TABLE distribusi DROP CONSTRAINT IF EXISTS distribusi_status_check');
        DB::statement("ALTER TABLE distribusi ADD CONSTRAINT distribusi_status_check CHECK (status IN ('Menunggu Driver', 'Driver Ditugaskan', 'Dalam Penjemputan', 'Dalam Pengiriman', 'Menunggu Verifikasi', 'Selesai', 'Dibatalkan'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE distribusi DROP CONSTRAINT IF EXISTS distribusi_status_check');
    }
};