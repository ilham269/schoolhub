<?php

use Illuminate\Support\Facades\Route;

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
