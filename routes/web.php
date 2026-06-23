<?php

use Illuminate\Support\Facades\Route;

Route::get('/gas-migrasi', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return "Mantap! Semua tabel sukses dibuat di Supabase.";
});
