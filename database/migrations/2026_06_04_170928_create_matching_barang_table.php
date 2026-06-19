<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matching_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang_donasi')->cascadeOnDelete();
            $table->foreignId('permintaan_id')->constrained('permintaan_barang')->cascadeOnDelete();
            $table->decimal('skor_kecocokan', 5, 2)->default(0);
            $table->unsignedInteger('jumlah_rekomendasi');
            $table->enum('status', ['Direkomendasikan', 'Disetujui', 'Ditolak'])->default('Direkomendasikan');
            $table->timestamps();
            $table->unique(['barang_id', 'permintaan_id']);
            $table->index('status');
            $table->index('skor_kecocokan');
            $table->index(['barang_id', 'status']);
            $table->index(['permintaan_id', 'status']);
        });

        DB::statement("
            ALTER TABLE matching_barang
            ADD CONSTRAINT chk_skor_kecocokan
            CHECK (skor_kecocokan >= 0 AND skor_kecocokan <= 100)
        ");

        DB::statement("
            ALTER TABLE matching_barang
            ADD CONSTRAINT chk_jumlah_rekomendasi
            CHECK (jumlah_rekomendasi > 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('matching_barang');
    }
};