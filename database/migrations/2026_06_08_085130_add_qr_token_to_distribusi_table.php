<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribusi', function (Blueprint $table) {

            $table->string('qr_token', 255)
                ->nullable()
                ->unique()
                ->after('verified_by');

        });
    }

    public function down(): void
    {
        Schema::table('distribusi', function (Blueprint $table) {

            $table->dropColumn('qr_token');

        });
    }
};