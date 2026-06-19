<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribusi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')
                ->constrained('barang_donasi')
                ->cascadeOnDelete();
            $table->foreignId('permintaan_id')
                ->constrained('permintaan_barang')
                ->cascadeOnDelete();
            $table->foreignId('driver_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('admin_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->date('tanggal_pickup');
            $table->date('tanggal_pengiriman');
            $table->unsignedInteger('jumlah_disalurkan');
            $table->enum('status', [
                'Dijadwalkan',
                'Diambil',
                'Dikirim',
                'Selesai',
                'Dibatalkan'
            ])->default('Dijadwalkan');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('driver_id');
            $table->index('admin_id');
            $table->index('barang_id');
            $table->index('permintaan_id');
        });

        DB::statement("
            ALTER TABLE distribusi
            ADD CONSTRAINT chk_jumlah_disalurkan
            CHECK (jumlah_disalurkan > 0)
        ");

        // CHECK constraint tanggal_pengiriman >= tanggal_pickup
        DB::statement("
            ALTER TABLE distribusi
            ADD CONSTRAINT chk_distribusi_tanggal
            CHECK (tanggal_pengiriman >= tanggal_pickup)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('distribusi');
    }
};