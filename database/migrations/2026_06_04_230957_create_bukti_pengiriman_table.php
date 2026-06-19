<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukti_pengiriman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_id')
                ->constrained('distribusi')
                ->cascadeOnDelete();
            $table->string('foto_bukti');
            $table->string('nama_penerima', 100);
            $table->text('catatan')->nullable();
            $table->timestamp('waktu_serah_terima');
            $table->string('qr_token', 255)->nullable()->unique(); // unique constraint
            $table->timestamps();

            $table->unique('distribusi_id');
            $table->index('waktu_serah_terima');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukti_pengiriman');
    }
};