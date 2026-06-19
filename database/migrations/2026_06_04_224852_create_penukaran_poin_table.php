<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penukaran_poin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donatur_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('voucher_id')
                ->constrained('voucher')
                ->restrictOnDelete();
            $table->unsignedInteger('poin_digunakan');
            $table->enum('status', [
                'Menunggu',
                'Disetujui',
                'Ditolak'
            ])->default('Menunggu');
            $table->timestamp('tanggal_penukaran')->useCurrent();
            $table->timestamps();

            $table->index('status');
            $table->index('donatur_id'); // tambahan index untuk query dashboard donor
        });

        DB::statement("
            ALTER TABLE penukaran_poin
            ADD CONSTRAINT chk_poin_digunakan
            CHECK (poin_digunakan > 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('penukaran_poin');
    }
};