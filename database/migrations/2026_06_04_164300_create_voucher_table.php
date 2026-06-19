<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voucher', function (Blueprint $table) {
            $table->id();
            $table->string('nama_voucher', 100);
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('poin_dibutuhkan'); // diubah dari integer
            $table->unsignedInteger('stok')->default(0); // diubah dari integer
            $table->enum('status', [
                'Aktif',
                'Nonaktif'
            ])->default('Aktif');
            $table->timestamps();
        });

        // CHECK constraint: stok harus >= 0 (opsional karena unsigned, tapi tetap aman)
        DB::statement("
            ALTER TABLE voucher
            ADD CONSTRAINT chk_voucher_stok
            CHECK (stok >= 0)
        ");

        // CHECK constraint: poin_dibutuhkan harus > 0
        DB::statement("
            ALTER TABLE voucher
            ADD CONSTRAINT chk_voucher_poin
            CHECK (poin_dibutuhkan > 0)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher');
    }
};