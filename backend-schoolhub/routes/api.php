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
use App\Http\Controllers\Api\PpdbController;
use App\Http\Controllers\Api\PaymentCallbackController;
use App\Http\Controllers\Api\MapelController;
use App\Http\Controllers\Api\SubjekGuruController;
use App\Http\Controllers\Api\SubjekKelasController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\MuridNilaiController;
use App\Http\Controllers\Api\PublicHomeController;
use App\Http\Controllers\Api\MateriController;
use App\Http\Controllers\Api\MuridTugasController;

// No session/auth middleware: notification is sent server-to-server by Midtrans.
Route::post('/payment/callback', [PaymentCallbackController::class, 'callback'])->name('payment.callback');

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
    Route::get('/home', [PublicHomeController::class, 'home']);
    Route::get('/guru', [PublicHomeController::class, 'guru']);
    Route::get('/programs', [PublicHomeController::class, 'programs']);
    Route::post('/ppdb/register', [PpdbController::class, 'register']);
    
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
  

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('ppdb')->group(function () {
        // Routes for calon siswa only
        Route::middleware('role:murid,calon_siswa')->group(function () {
            Route::get('/profile', [PpdbController::class, 'profile']);
            Route::get('/exams', [PpdbController::class, 'exams']);
            
            // Exam session endpoints with rate limiting
            Route::post('/exams/{exam}/start', [PpdbController::class, 'start'])
                ->middleware('rate_limit_exam:5');
            Route::post('/exams/{exam}/submit', [PpdbController::class, 'submit'])
                ->middleware('rate_limit_exam:3');
            
            // Session management for students
            Route::post('/auto-save', [PpdbController::class, 'autoSave'])
                ->middleware('rate_limit_exam:120');
            Route::post('/log-activity', [PpdbController::class, 'logActivity'])
                ->middleware('rate_limit_exam:180');
            Route::get('/session/{sessionId}/status', [PpdbController::class, 'sessionStatus'])
                ->middleware('rate_limit_exam:30');
        });
        
        // Teacher/Admin management
        Route::middleware('role:admin,guru')->group(function () {
            Route::get('/manage/exams', [PpdbController::class, 'manageExams']);
            Route::post('/manage/exams', [PpdbController::class, 'storeExam']);
            Route::patch('/manage/exams/{exam}', [PpdbController::class, 'updateExam']);
            Route::post('/manage/exams/{exam}/questions', [PpdbController::class, 'storeQuestion']);
            Route::get('/manage/candidates', [PpdbController::class, 'candidates']);
            Route::patch('/manage/candidates/{calon}', [PpdbController::class, 'updateCandidate']);
            
            // Monitoring (teachers/admin)
            Route::get('/manage/security-dashboard', [PpdbController::class, 'securityDashboard']);
            Route::get('/manage/sessions/{sessionId}/logs', [PpdbController::class, 'sessionActivityLogs']);
            Route::get('/manage/exams/{exam}/monitor', [PpdbController::class, 'monitorSessions']);
            Route::get('/manage/sessions/{sessionId}/activity-log', [PpdbController::class, 'sessionActivityLog']);
        });
        
        // Admin only routes
        Route::middleware('role:admin')->group(function () {
            Route::post('/manage/candidates/{calon}/account', [PpdbController::class, 'createAccount']);
            Route::post('/manage/sessions/{sessionId}/suspend', [PpdbController::class, 'suspendSession']);
        });
    });
    
    // Dashboard Routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/admin', [DashboardController::class, 'admin']);
        Route::get('/guru', [DashboardController::class, 'guru']);
        Route::get('/murid', [DashboardController::class, 'murid']);
        Route::get('/karyawan', [DashboardController::class, 'karyawan']);
        Route::get('/report', [DashboardController::class, 'report']);
    });

    // Materi is deliberately separate from Tugas: only its creator may manage it.
    Route::prefix('guru')->middleware('role:guru')->group(function () {
        Route::get('/materi', [MateriController::class, 'index']);
        Route::post('/materi', [MateriController::class, 'store']);
        Route::get('/materi/{materi}', [MateriController::class, 'show']);
        Route::put('/materi/{materi}', [MateriController::class, 'update']);
        Route::delete('/materi/{materi}', [MateriController::class, 'destroy']);
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
        Route::get('/kategori/{kategori}', [BeritaController::class, 'byKategori']);
        Route::get('/{id}', [BeritaController::class, 'show']);
        Route::put('/{id}', [BeritaController::class, 'update']);
        Route::delete('/{id}', [BeritaController::class, 'destroy']);
    });

      Route::prefix('guru')->group(function () {
        Route::get('/', [GuruController::class, 'index']);
        Route::post('/', [GuruController::class, 'store']);
        Route::get('/{id}', [GuruController::class, 'show']);
        Route::put('/{id}', [GuruController::class, 'update']);
        Route::delete('/{id}', [GuruController::class, 'destroy']);
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
        Route::middleware('role:murid')->group(function () {
            Route::get('/tugas', [MuridTugasController::class, 'index']);
            Route::post('/tugas/{tugas}/kumpulkan', [MuridTugasController::class, 'submit']);
            Route::get('/materi', [MateriController::class, 'index']);
            Route::get('/materi/{materi}', [MateriController::class, 'show']);
            Route::get('/materi/{materi}/download', [MateriController::class, 'download']);
        });
        Route::get('/', [MuridController::class, 'index']);
        Route::post('/', [MuridController::class, 'store']);
        Route::get('/profile', [MuridController::class, 'myProfile']);
        Route::put('/profile', [MuridController::class, 'updateMyProfile']);
        Route::get('/nilai', [MuridNilaiController::class, 'index'])->middleware('role:murid');
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

    // Academic Core — readable by admin, guru, murid
    Route::middleware('role:admin,guru,murid')->group(function () {
        Route::get('/mapel', [MapelController::class, 'index']);
        Route::get('/mapel/active', [MapelController::class, 'active']);
        Route::get('/mapel/kelas/{kelasId}', [MapelController::class, 'byKelas']);
        Route::get('/mapel/guru/{guruId}', [MapelController::class, 'byGuru']);
        Route::get('/mapel/{id}', [MapelController::class, 'show']);

        Route::get('/subjek-guru', [SubjekGuruController::class, 'index']);
        Route::get('/subjek-kelas', [SubjekKelasController::class, 'index']);

        Route::get('/jadwal', [JadwalController::class, 'index']);
        Route::get('/jadwal/kelas/{kelasId}', [JadwalController::class, 'byKelas']);
        Route::get('/jadwal/guru/{guruId}', [JadwalController::class, 'byGuru']);
        Route::get('/jadwal/hari/{hari}', [JadwalController::class, 'byHari']);
        Route::get('/jadwal/{id}', [JadwalController::class, 'show']);
    });

    // Academic Core — admin mutations
    Route::middleware('role:admin')->group(function () {
        Route::post('/mapel', [MapelController::class, 'store']);
        Route::put('/mapel/{id}', [MapelController::class, 'update']);
        Route::delete('/mapel/{id}', [MapelController::class, 'destroy']);

        Route::post('/subjek-guru', [SubjekGuruController::class, 'store']);
        Route::delete('/subjek-guru/{id}', [SubjekGuruController::class, 'destroy']);

        Route::post('/subjek-kelas', [SubjekKelasController::class, 'store']);
        Route::delete('/subjek-kelas/{id}', [SubjekKelasController::class, 'destroy']);

        Route::post('/jadwal', [JadwalController::class, 'store']);
        Route::put('/jadwal/{id}', [JadwalController::class, 'update']);
        Route::delete('/jadwal/{id}', [JadwalController::class, 'destroy']);
    });

    // Keuangan Routes (Karyawan & Admin)
    Route::prefix('keuangan')->middleware('role:karyawan,Admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Api\KeuanganController::class, 'dashboard']);
        
        // Tagihan SPP Management
        Route::get('/tagihan', [\App\Http\Controllers\Api\KeuanganController::class, 'getTagihanSpp']);
        Route::post('/tagihan', [\App\Http\Controllers\Api\KeuanganController::class, 'createTagihan']);
        Route::put('/tagihan/{id}', [\App\Http\Controllers\Api\KeuanganController::class, 'updateTagihan']);
        Route::delete('/tagihan/{id}', [\App\Http\Controllers\Api\KeuanganController::class, 'deleteTagihan']);
        
        // Slip Gaji Management
        Route::get('/slip-gaji', [\App\Http\Controllers\Api\KeuanganController::class, 'getSlipGaji']);
        Route::post('/slip-gaji', [\App\Http\Controllers\Api\KeuanganController::class, 'createSlipGaji']);
        Route::put('/slip-gaji/{id}', [\App\Http\Controllers\Api\KeuanganController::class, 'updateSlipGaji']);
        Route::post('/slip-gaji/{id}/approve', [\App\Http\Controllers\Api\KeuanganController::class, 'approveSlipGaji']);
        Route::post('/slip-gaji/{id}/mark-paid', [\App\Http\Controllers\Api\KeuanganController::class, 'markAsPaid']);
        Route::delete('/slip-gaji/{id}', [\App\Http\Controllers\Api\KeuanganController::class, 'deleteSlipGaji']);
    });
});
