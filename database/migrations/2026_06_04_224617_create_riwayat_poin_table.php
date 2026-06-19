<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_poin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donatur_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('distribusi_id')
                ->constrained('distribusi')
                ->cascadeOnDelete();
            $table->unsignedInteger('jumlah_poin');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->index('donatur_id');
            $table->unique('distribusi_id'); // tambahan: satu distribusi hanya satu riwayat poin
        });

        // CHECK constraint jumlah_poin > 0
        DB::statement("
            ALTER TABLE riwayat_poin
            ADD CONSTRAINT chk_jumlah_poin
            CHECK (jumlah_poin > 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_poin');
    }
};