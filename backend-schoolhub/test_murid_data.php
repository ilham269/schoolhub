<?php

/**
 * Script untuk test apakah data murid, tugas, dan materi sudah benar
 * Jalankan: php test_murid_data.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 Cek Data untuk Murid...\n\n";

// 1. Cek jumlah murid
$muridCount = DB::table('murids')->count();
echo "1️⃣  Jumlah Murid: {$muridCount}\n";

if ($muridCount == 0) {
    echo "   ❌ TIDAK ADA murid! Jalankan: php artisan db:seed --class=MuridSeeder\n\n";
    exit;
}

// 2. Sample murid pertama
$murid = DB::table('murids')
    ->join('users', 'users.id', '=', 'murids.user_id')
    ->select('murids.id as murid_id', 'murids.kelas_id', 'murids.nama_lengkap_murid', 'users.email')
    ->first();

echo "\n2️⃣  Sample Murid:\n";
echo "   Nama: {$murid->nama_lengkap_murid}\n";
echo "   Email: {$murid->email}\n";
echo "   Kelas ID: {$murid->kelas_id}\n";

// 3. Cek kelas murid
$kelas = DB::table('kelas')->where('id', $murid->kelas_id)->first();
echo "   Kelas: {$kelas->nama_kelas}\n";

// 4. Cek tugas untuk kelas murid
echo "\n3️⃣  Tugas untuk Kelas ID {$murid->kelas_id}:\n";
$tugasForKelas = DB::table('tugas')
    ->where('kelas_id', $murid->kelas_id)
    ->where('is_active', true)
    ->get(['id', 'judul', 'mapel_id', 'deadline']);

if ($tugasForKelas->isEmpty()) {
    echo "   ❌ TIDAK ADA tugas untuk kelas {$kelas->nama_kelas}!\n";
    echo "   TugasDummySeeder mungkin menggunakan kelas_id yang berbeda.\n\n";
    
    // Show semua tugas yang ada
    $allTugas = DB::table('tugas')
        ->join('kelas', 'kelas.id', '=', 'tugas.kelas_id')
        ->select('tugas.id', 'tugas.judul', 'tugas.kelas_id', 'kelas.nama_kelas')
        ->get();
    
    echo "   📋 Semua tugas di database:\n";
    foreach ($allTugas as $t) {
        echo "      - {$t->judul} (Kelas ID: {$t->kelas_id} - {$t->nama_kelas})\n";
    }
} else {
    echo "   ✅ Ada {$tugasForKelas->count()} tugas:\n";
    foreach ($tugasForKelas as $t) {
        echo "      - {$t->judul}\n";
    }
}

// 5. Cek materi untuk kelas murid
echo "\n4️⃣  Materi untuk Kelas ID {$murid->kelas_id}:\n";
$materiForKelas = DB::table('materis')
    ->where('kelas_id', $murid->kelas_id)
    ->where('is_published', true)
    ->get(['id', 'judul', 'mapel_id']);

if ($materiForKelas->isEmpty()) {
    echo "   ❌ TIDAK ADA materi untuk kelas {$kelas->nama_kelas}!\n";
    echo "   MateriSeeder mungkin menggunakan kelas_id yang berbeda.\n\n";
    
    // Show semua materi yang ada
    $allMateri = DB::table('materis')
        ->join('kelas', 'kelas.id', '=', 'materis.kelas_id')
        ->select('materis.id', 'materis.judul', 'materis.kelas_id', 'kelas.nama_kelas')
        ->get();
    
    echo "   📋 Semua materi di database:\n";
    foreach ($allMateri as $m) {
        echo "      - {$m->judul} (Kelas ID: {$m->kelas_id} - {$m->nama_kelas})\n";
    }
} else {
    echo "   ✅ Ada {$materiForKelas->count()} materi:\n";
    foreach ($materiForKelas as $m) {
        echo "      - {$m->judul}\n";
    }
}

// 6. Cek pengumpulan tugas & nilai untuk murid ini
echo "\n5️⃣  Pengumpulan Tugas & Nilai untuk Murid:\n";
$pengumpulan = DB::table('pengumpulan_tugas')
    ->join('tugas', 'tugas.id', '=', 'pengumpulan_tugas.tugas_id')
    ->where('pengumpulan_tugas.murid_id', $murid->murid_id)
    ->select('tugas.judul', 'pengumpulan_tugas.nilai', 'pengumpulan_tugas.status')
    ->get();

if ($pengumpulan->isEmpty()) {
    echo "   ❌ Murid ini belum mengumpulkan tugas apapun.\n";
} else {
    echo "   ✅ Ada {$pengumpulan->count()} pengumpulan:\n";
    foreach ($pengumpulan as $p) {
        echo "      - {$p->judul}: Nilai {$p->nilai} ({$p->status})\n";
    }
}

// 7. Summary & Solusi
echo "\n" . str_repeat("=", 70) . "\n";
echo "SUMMARY & SOLUSI:\n";
echo str_repeat("=", 70) . "\n";

$issues = [];

if ($tugasForKelas->isEmpty()) {
    $issues[] = "Tugas tidak ada untuk kelas murid";
    echo "⚠️  MASALAH: Tugas tidak match dengan kelas murid\n";
    echo "   SOLUSI: Jalankan ulang seeder:\n";
    echo "   php artisan db:seed --class=TugasDummySeeder --force\n\n";
}

if ($materiForKelas->isEmpty()) {
    $issues[] = "Materi tidak ada untuk kelas murid";
    echo "⚠️  MASALAH: Materi tidak match dengan kelas murid\n";
    echo "   SOLUSI: Jalankan ulang seeder:\n";
    echo "   php artisan db:seed --class=MateriSeeder --force\n\n";
}

if ($pengumpulan->isEmpty()) {
    $issues[] = "Belum ada pengumpulan/nilai";
    echo "ℹ️  INFO: Murid belum ada pengumpulan tugas & nilai\n";
    echo "   Ini normal jika baru seed. TugasDummySeeder seharusnya auto-generate.\n\n";
}

if (empty($issues)) {
    echo "✅ SEMUA DATA SUDAH BENAR!\n";
    echo "   Murid harusnya bisa lihat tugas, materi, dan nilai.\n\n";
    echo "💡 Tips Login Murid:\n";
    echo "   Email: {$murid->email}\n";
    echo "   Password: password\n\n";
} else {
    echo "❌ Ada {" . count($issues) . "} masalah yang perlu diperbaiki.\n";
    echo "   Jalankan solusi di atas, lalu test ulang.\n\n";
}

echo "Selesai!\n";
