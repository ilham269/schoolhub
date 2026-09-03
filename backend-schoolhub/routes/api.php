<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuruController;
use App\Http\Controllers\Api\MuridController;
use App\Http\Controllers\Api\KaryawanController;
use App\Http\Controllers\Api\KelasController;
use App\Http\Controllers\Api\PengumumanController;
use App\Http\Controllers\Api\BeritaController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MapelController;
use App\Http\Controllers\Api\JadwalController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Auth Routes
Route::prefix('auth')->group(function () {
    // Public
    Route::post('/login', [AuthController::class, 'login']);

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Public Routes (tanpa auth)
Route::prefix('public')->group(function () {
    // Pengumuman
    Route::get('/pengumuman', [PengumumanController::class, 'published']);
    Route::get('/pengumuman/{id}', [PengumumanController::class, 'show']);
    
    // Berita
    Route::get('/berita', [BeritaController::class, 'published']);
    Route::get('/berita/latest/{limit?}', [BeritaController::class, 'latest']);
    Route::get('/berita/slug/{slug}', [BeritaController::class, 'showBySlug']);
    Route::get('/berita/{id}', [BeritaController::class, 'show']);
});

    // Guru Routes
    Route::prefix('guru')->group(function () {
        Route::get('/', [GuruController::class, 'index']);
        Route::post('/', [GuruController::class, 'store']);
        Route::get('/{id}', [GuruController::class, 'show']);
        Route::put('/{id}', [GuruController::class, 'update']);
        Route::delete('/{id}', [GuruController::class, 'destroy']);
    });

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Dashboard Routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/admin', [DashboardController::class, 'admin']);
        Route::get('/guru', [DashboardController::class, 'guru']);
        Route::get('/murid', [DashboardController::class, 'murid']);
        Route::get('/karyawan', [DashboardController::class, 'karyawan']);
        Route::get('/report', [DashboardController::class, 'report']);
    });

    // Pengumuman Routes
    Route::prefix('pengumuman')->group(function () {
        Route::get('/', [PengumumanController::class, 'index']);
        Route::post('/', [PengumumanController::class, 'store']);
        Route::get('/{id}', [PengumumanController::class, 'show']);
        Route::put('/{id}', [PengumumanController::class, 'update']);
        Route::delete('/{id}', [PengumumanController::class, 'destroy']);
        Route::get('/kategori/{kategori}', [PengumumanController::class, 'byKategori']);
    });

    // Berita Routes
    Route::prefix('berita')->group(function () {
        Route::get('/', [BeritaController::class, 'index']);
        Route::post('/', [BeritaController::class, 'store']);
        Route::get('/{id}', [BeritaController::class, 'show']);
        Route::put('/{id}', [BeritaController::class, 'update']);
        Route::delete('/{id}', [BeritaController::class, 'destroy']);
        Route::get('/kategori/{kategori}', [BeritaController::class, 'byKategori']);
    });

    // Settings Routes
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::get('/{key}', [SettingController::class, 'show']);
        Route::put('/app', [SettingController::class, 'updateApp']);
        Route::put('/akademik', [SettingController::class, 'updateAkademik']);
        Route::put('/sistem', [SettingController::class, 'updateSistem']);
        Route::put('/email', [SettingController::class, 'updateEmail']);
        Route::put('/notification', [SettingController::class, 'updateNotification']);
        Route::post('/reset/{key}', [SettingController::class, 'reset']);
    });
    

    // Murid Routes
    Route::prefix('murid')->group(function () {
        Route::get('/', [MuridController::class, 'index']);
        Route::post('/', [MuridController::class, 'store']);
        Route::get('/{id}', [MuridController::class, 'show']);
        Route::put('/{id}', [MuridController::class, 'update']);
        Route::delete('/{id}', [MuridController::class, 'destroy']);
        Route::get('/kelas/{kelasId}', [MuridController::class, 'byKelas']);
    });

    // Karyawan Routes
    Route::prefix('karyawan')->group(function () {
        Route::get('/', [KaryawanController::class, 'index']);
        Route::post('/', [KaryawanController::class, 'store']);
        Route::get('/{id}', [KaryawanController::class, 'show']);
        Route::put('/{id}', [KaryawanController::class, 'update']);
        Route::delete('/{id}', [KaryawanController::class, 'destroy']);
        Route::get('/bagian/{bagian}', [KaryawanController::class, 'byBagian']);
    });

    // Kelas Routes
    Route::prefix('kelas')->group(function () {
        Route::get('/', [KelasController::class, 'index']);
        Route::post('/', [KelasController::class, 'store']);
        Route::get('/{id}', [KelasController::class, 'show']);
        Route::put('/{id}', [KelasController::class, 'update']);
        Route::delete('/{id}', [KelasController::class, 'destroy']);
        Route::get('/jurusan/{jurusan}', [KelasController::class, 'byJurusan']);
        Route::get('/tingkat/{tingkat}', [KelasController::class, 'byTingkat']);
    });

    // Mapel Routes
    Route::prefix('mapel')->group(function () {
        Route::get('/', [MapelController::class, 'index']);
        Route::post('/', [MapelController::class, 'store']);
        Route::get('/active', [MapelController::class, 'active']);
        Route::get('/{id}', [MapelController::class, 'show']);
        Route::put('/{id}', [MapelController::class, 'update']);
        Route::delete('/{id}', [MapelController::class, 'destroy']);
        Route::post('/assign-kelas', [MapelController::class, 'assignToKelas']);
        Route::post('/assign-guru', [MapelController::class, 'assignToGuru']);
        Route::get('/kelas/{kelasId}', [MapelController::class, 'byKelas']);
        Route::get('/guru/{guruId}', [MapelController::class, 'byGuru']);
    });

    // Jadwal Routes
    Route::prefix('jadwal')->group(function () {
        Route::get('/', [JadwalController::class, 'index']);
        Route::post('/', [JadwalController::class, 'store']);
        Route::get('/{id}', [JadwalController::class, 'show']);
        Route::put('/{id}', [JadwalController::class, 'update']);
        Route::delete('/{id}', [JadwalController::class, 'destroy']);
        Route::get('/kelas/{kelasId}', [JadwalController::class, 'byKelas']);
        Route::get('/guru/{guruId}', [JadwalController::class, 'byGuru']);
        Route::get('/hari/{hari}', [JadwalController::class, 'byHari']);
    });

});

