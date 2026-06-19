<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribusi', function (Blueprint $table) {
            // Tambahkan qr_token jika belum
            if (!Schema::hasColumn('distribusi', 'qr_token')) {
                $table->string('qr_token')->nullable()->unique()->after('catatan');
            }

            // Tambahkan verified_by jika belum
            if (!Schema::hasColumn('distribusi', 'verified_by')) {
                $table->foreignId('verified_by')
                    ->nullable()
                    ->after('admin_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            // Tambahkan qr_verified_at jika belum
            if (!Schema::hasColumn('distribusi', 'qr_verified_at')) {
                $table->timestamp('qr_verified_at')->nullable()->after('verified_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('distribusi', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'verified_by', 'qr_verified_at']);
        });
    }
};