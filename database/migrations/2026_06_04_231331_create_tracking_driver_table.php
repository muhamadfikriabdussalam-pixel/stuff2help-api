<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_driver', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_id')
                ->constrained('distribusi')
                ->cascadeOnDelete();
            $table->foreignId('driver_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestampTz('waktu_lokasi');
            $table->timestamps();

            $table->index('driver_id');
            $table->index('distribusi_id');
            $table->index(['driver_id', 'waktu_lokasi']);
            $table->index(['distribusi_id', 'waktu_lokasi']); // tambahan untuk query distribusi
        });

        DB::statement("
            ALTER TABLE tracking_driver
            ADD CONSTRAINT chk_latitude
            CHECK (latitude >= -90 AND latitude <= 90)
        ");

        DB::statement("
            ALTER TABLE tracking_driver
            ADD CONSTRAINT chk_longitude
            CHECK (longitude >= -180 AND longitude <= 180)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_driver');
    }
};