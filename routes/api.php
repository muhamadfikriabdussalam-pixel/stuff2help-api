<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangDonasiController;
use App\Http\Controllers\Api\PermintaanBarangController;
use App\Http\Controllers\Api\MatchingBarangController;
use App\Http\Controllers\Api\DistribusiController;
use App\Http\Controllers\Api\TrackingDriverController;
use App\Http\Controllers\Api\BuktiPengirimanController;
use App\Http\Controllers\Api\RatingDriverController;
use App\Http\Controllers\Api\RewardPointController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\QrVerificationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PenukaranPoinController;

/*
|--------------------------------------------------------------------------
| Public Routes (tanpa autentikasi)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google-sync', [AuthController::class, 'syncGoogleUser']); // ✅ Tambahkan

/*
|--------------------------------------------------------------------------
| Protected Routes (memerlukan token Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']); // ✅ Tambahkan route update profil
    Route::post('/logout', [AuthController::class, 'logout']);

    // User listing (filter role)
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);

    // CRUD Barang Donasi
    Route::apiResource('barang-donasi', BarangDonasiController::class);

    // CRUD Permintaan Barang
    Route::apiResource('permintaan-barang', PermintaanBarangController::class);

    // Distribusi: semua user login bisa melihat
    Route::get('/distribusi', [DistribusiController::class, 'index']);
    Route::get('/distribusi/{distribusi}', [DistribusiController::class, 'show']);

    // Histori Tracking
    Route::get('/tracking/distribusi/{id}', [TrackingDriverController::class, 'history']);

    // Lihat Bukti Pengiriman
    Route::get('/bukti-pengiriman/{distribusiId}', [BuktiPengirimanController::class, 'show']);

    // Lihat Rating Driver
    Route::get('/rating-driver/{distribusiId}', [RatingDriverController::class, 'show']);

    // Voucher (Lihat daftar & detail)
    Route::get('/voucher', [VoucherController::class, 'index']);
    Route::get('/voucher/{id}', [VoucherController::class, 'show']);

    // Reward Point
    Route::get('/reward-points', [RewardPointController::class, 'index']);
    Route::get('/reward-points/{id}', [RewardPointController::class, 'show']);

    // Penukaran Poin (Riwayat Voucher)
    Route::get('/penukaran-poin', [PenukaranPoinController::class, 'index']);
    Route::get('/penukaran-poin/{id}', [PenukaranPoinController::class, 'show']);

    // Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'show']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    // Dashboard
    Route::get('/dashboard/admin', [DashboardController::class, 'admin']);
    Route::get('/dashboard/donatur', [DashboardController::class, 'donatur']);
    Route::get('/dashboard/driver', [DashboardController::class, 'driver']);
    Route::get('/dashboard/penerima', [DashboardController::class, 'dashboardPenerima']);

    // QR Code Routes (tanpa verifikasi)
    Route::prefix('qr')->group(function () {
        Route::get('/{distribusi}', [QrVerificationController::class, 'show']);
    });
});

// ========== Admin Only ==========
Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    // Distribusi (Create, Update, Delete)
    Route::post('/distribusi', [DistribusiController::class, 'store']);
    Route::put('/distribusi/{distribusi}', [DistribusiController::class, 'update']);
    Route::delete('/distribusi/{distribusi}', [DistribusiController::class, 'destroy']);

    // QR Generate (Admin)
    Route::post('/qr/generate/{distribusi}', [QrVerificationController::class, 'generate']);

    // Smart Matching Engine
    Route::post('/matching/generate', [MatchingBarangController::class, 'generate']);
    Route::get('/matching', [MatchingBarangController::class, 'index']);
    Route::get('/matching/{matchingBarang}', [MatchingBarangController::class, 'show']);
    Route::put('/matching/{matchingBarang}/approve', [MatchingBarangController::class, 'approve']);
    Route::put('/matching/{matchingBarang}/reject', [MatchingBarangController::class, 'reject']);

    // Voucher CRUD (Admin)
    Route::post('/voucher', [VoucherController::class, 'store']);
    Route::put('/voucher/{id}', [VoucherController::class, 'update']);
    Route::delete('/voucher/{id}', [VoucherController::class, 'destroy']);
});

// ========== Driver Only ==========
Route::middleware(['auth:sanctum', 'role:Driver'])->group(function () {
    Route::post('/tracking/start-pickup', [TrackingDriverController::class, 'startPickup']);
    Route::post('/tracking/start-delivery', [TrackingDriverController::class, 'startDelivery']);
    Route::post('/tracking/update-location', [TrackingDriverController::class, 'updateLocation']);

    // Upload Bukti Pengiriman
    Route::post('/bukti-pengiriman', [BuktiPengirimanController::class, 'store']);
});

// ========== Penerima Only ==========
Route::middleware(['auth:sanctum', 'role:Penerima'])->group(function () {
    Route::post('/qr/verify', [QrVerificationController::class, 'verify']);
    Route::post('/rating-driver', [RatingDriverController::class, 'store']);
});

// ========== Donatur Only ==========
Route::middleware(['auth:sanctum', 'role:Donatur'])->group(function () {
    Route::post('/voucher/redeem', [VoucherController::class, 'redeem']);
});

// ========== Testing Routes (opsional) ==========
Route::middleware(['auth:sanctum', 'role:Admin,Driver'])->group(function () {
    Route::get('/dashboard-operasional', function () {
        return response()->json(['message' => 'Admin & Driver']);
    });
});