<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_donasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donatur_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('kategori_id')
                ->constrained('kategori_barang')
                ->restrictOnDelete();
            $table->string('nama_barang', 150);
            $table->text('deskripsi')->nullable();
            $table->text('foto_url')->nullable();
            $table->unsignedInteger('jumlah'); // total stok awal
            $table->unsignedInteger('jumlah_tersedia'); // stok yang belum disalurkan
            $table->unsignedInteger('jumlah_terdistribusi')->default(0); // total sudah disalurkan
            $table->enum('kondisi', [
                'Baik',
                'Cukup',
                'Perlu Perbaikan'
            ]);
            $table->enum('status', [
                'Menunggu Pencocokkan',
                'Tercocokkan',
                'Penjemputan',
                'Pengiriman',
                'Selesai',
                'Dibatalkan'
            ])->default('Menunggu Pencocokkan');
            $table->timestamps();

            $table->index('status');
            $table->index('donatur_id');
            $table->index('kategori_id');
        });

        // CHECK 1: jumlah > 0
        DB::statement("
            ALTER TABLE barang_donasi
            ADD CONSTRAINT chk_barang_donasi_jumlah
            CHECK (jumlah > 0)
        ");

        // CHECK 2: jumlah_tersedia >= 0
        DB::statement("
            ALTER TABLE barang_donasi
            ADD CONSTRAINT chk_barang_donasi_jumlah_tersedia
            CHECK (jumlah_tersedia >= 0)
        ");

        // CHECK 3: jumlah_tersedia <= jumlah
        DB::statement("
            ALTER TABLE barang_donasi
            ADD CONSTRAINT chk_barang_stok_valid
            CHECK (jumlah_tersedia <= jumlah)
        ");

        // CHECK 4: jumlah_terdistribusi <= jumlah
        DB::statement("
            ALTER TABLE barang_donasi
            ADD CONSTRAINT chk_barang_terdistribusi
            CHECK (jumlah_terdistribusi <= jumlah)
        ");

        // CHECK 5: jumlah_tersedia + jumlah_terdistribusi = jumlah
        DB::statement("
            ALTER TABLE barang_donasi
            ADD CONSTRAINT chk_barang_total
            CHECK (jumlah_tersedia + jumlah_terdistribusi = jumlah)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_donasi');
    }
};