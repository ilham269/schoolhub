<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Murid\PaymentController;
use App\Http\Controllers\Karyawan\KeuanganController;
use App\Http\Controllers\Karyawan\SlipGajiController;

Route::get('/', function () {
    return response()->json([
        'message' => 'SchoolHub API',
        'version' => '1.0.0',
        'status' => 'running',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'Test route works!',
        'data' => [
            'users' => \App\Models\User::count(),
            'kelas' => \App\Models\Kelas::count(),
            'guru' => \App\Models\Guru::count(),
            'murid' => \App\Models\Murid::count(),
            'karyawan' => \App\Models\Karyawan::count(),
        ],
    ]);
});

Route::middleware(['auth', 'role:Murid'])->prefix('murid')->group(function () {
    Route::get('/tagihan', [PaymentController::class, 'index'])->name('murid.tagihan.index');
    Route::get('/tagihan/riwayat', [PaymentController::class, 'riwayat'])->name('murid.tagihan.riwayat');
    Route::post('/tagihan/{tagihan}/bayar', [PaymentController::class, 'bayar'])->name('murid.tagihan.bayar');
    Route::get('/tagihan/{tagihan}/kuitansi', [PaymentController::class, 'downloadKuitansi'])->name('murid.tagihan.kuitansi');
});

Route::middleware(['auth', 'role:Karyawan'])->prefix('karyawan/keuangan')->group(function () {
    Route::get('/', [KeuanganController::class, 'dashboard'])->name('karyawan.keuangan.dashboard');
    Route::get('/tagihan', [KeuanganController::class, 'daftarTagihan'])->name('karyawan.keuangan.tagihan');
    Route::get('/tagihan/{tagihan}', [KeuanganController::class, 'detailTagihan'])->name('karyawan.keuangan.tagihan.detail');
    Route::post('/tagihan/{tagihan}/manual', [KeuanganController::class, 'updateManual'])->name('karyawan.keuangan.tagihan.manual');
    Route::post('/slip-gaji/{slipGaji}/approve', [SlipGajiController::class, 'approve'])->name('slip-gaji.approve');
    Route::post('/slip-gaji/{slipGaji}/paid', [SlipGajiController::class, 'markPaid'])->name('slip-gaji.paid');
    Route::resource('/slip-gaji', SlipGajiController::class);
});
