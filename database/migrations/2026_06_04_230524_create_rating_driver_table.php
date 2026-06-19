<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating_driver', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_id')
                ->constrained('distribusi')
                ->cascadeOnDelete();
            $table->foreignId('driver_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('pemberi_rating_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->decimal('rating', 3, 2);
            $table->text('ulasan')->nullable();
            $table->timestamps();

            $table->unique(['distribusi_id', 'pemberi_rating_id']);
            $table->index('driver_id'); // tambahan index untuk dashboard driver
        });

        DB::statement("
            ALTER TABLE rating_driver
            ADD CONSTRAINT chk_rating_driver
            CHECK (rating >= 1 AND rating <= 5)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('rating_driver');
    }
};