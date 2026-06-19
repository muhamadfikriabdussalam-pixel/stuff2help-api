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
        // Tabel users dengan struktur kustom
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('username', 50)->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', [
                'Donatur',
                'Penerima',
                'Driver',
                'Admin'
            ]);
            $table->string('no_hp', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('foto_profil')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->unsignedInteger('poin')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->rememberToken();
            $table->timestamps();

            // Index tambahan untuk performa query
            $table->index('role');
            $table->index('is_verified');
            $table->index('kota');
        });

        // CHECK constraint untuk poin >= 0
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT chk_users_poin
            CHECK (poin >= 0)
        ");

        // Tabel password_reset_tokens (standar Laravel)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Tabel sessions (standar Laravel)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};