<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerima_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('kategori_id')
                ->constrained('kategori_barang')
                ->restrictOnDelete();
            $table->string('judul_permintaan', 150);
            $table->unsignedInteger('jumlah');
            $table->unsignedInteger('jumlah_terpenuhi')->default(0);
            $table->text('deskripsi')->nullable();
            $table->enum('prioritas', [
                'Rendah',
                'Sedang',
                'Tinggi'
            ])->default('Sedang');
            $table->enum('status', [
                'Aktif',
                'Terpenuhi',
                'Dibatalkan'
            ])->default('Aktif');
            $table->timestamps();

            $table->index('status');
            $table->index('prioritas');
            $table->index('kategori_id');
            $table->index('penerima_id');
            $table->index(['status', 'kategori_id']); // composite index
        });

        DB::statement("
            ALTER TABLE permintaan_barang
            ADD CONSTRAINT chk_permintaan_jumlah
            CHECK (jumlah > 0)
        ");

        DB::statement("
            ALTER TABLE permintaan_barang
            ADD CONSTRAINT chk_permintaan_terpenuhi
            CHECK (jumlah_terpenuhi <= jumlah)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_barang');
    }
};