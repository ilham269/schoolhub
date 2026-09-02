<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuruController;
use App\Http\Controllers\Api\MuridController;
use App\Http\Controllers\Api\KaryawanController;
use App\Http\Controllers\Api\KelasController;

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

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Guru Routes
    Route::prefix('guru')->group(function () {
        Route::get('/', [GuruController::class, 'index']);
        Route::post('/', [GuruController::class, 'store']);
        Route::get('/{id}', [GuruController::class, 'show']);
        Route::put('/{id}', [GuruController::class, 'update']);
        Route::delete('/{id}', [GuruController::class, 'destroy']);
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

});

