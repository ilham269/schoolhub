<?php

/**
 * Script untuk test setup sistem tugas
 * Jalankan: php test_tugas_setup.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Memeriksa Setup Sistem Tugas...\n\n";

// 1. Cek tabel users dengan role guru
echo "1️⃣  Cek User dengan role Guru:\n";
$users = \App\Models\User::whereIn('role', ['guru', 'Guru'])->get(['id', 'name', 'email', 'role']);
if ($users->isEmpty()) {
    echo "   ❌ TIDAK ADA user dengan role guru!\n";
    echo "   Solusi: php artisan db:seed --class=GuruSeeder\n\n";
} else {
    foreach ($users as $user) {
        $guruData = $user->guru;
        echo "   ✅ User ID: {$user->id} | Name: {$user->name} | Role: {$user->role}\n";
        if ($guruData) {
            echo "      → Guru ID: {$guruData->id} | NIP: {$guruData->nip}\n";
        } else {
            echo "      ⚠️  TIDAK ADA data di tabel gurus!\n";
        }
    }
    echo "\n";
}

// 2. Cek tabel gurus
echo "2️⃣  Cek Tabel Gurus:\n";
$gurus = \App\Models\Guru::with('user')->get();
if ($gurus->isEmpty()) {
    echo "   ❌ Tabel gurus KOSONG!\n";
    echo "   Solusi: php artisan db:seed --class=GuruSeeder\n\n";
} else {
    echo "   ✅ Ada {$gurus->count()} data guru:\n";
    foreach ($gurus as $guru) {
        echo "      Guru ID: {$guru->id} | User ID: {$guru->user_id} | Nama: {$guru->nama_lengkap_guru}\n";
    }
    echo "\n";
}

// 3. Cek case-sensitivity role
echo "3️⃣  Cek Case-Sensitivity Role:\n";
$guruKapital = \App\Models\User::where('role', 'Guru')->count();
$guruLower = \App\Models\User::where('role', 'guru')->count();
echo "   Role 'Guru' (kapital): {$guruKapital}\n";
echo "   Role 'guru' (lowercase): {$guruLower}\n";

if ($guruKapital > 0 && $guruLower == 0) {
    echo "   ⚠️  Role menggunakan KAPITAL!\n";
    echo "   Middleware RoleMiddleware expect lowercase!\n";
    echo "   Solusi: Update role ke lowercase\n\n";
} elseif ($guruLower > 0) {
    echo "   ✅ Role sudah lowercase (benar!)\n\n";
}

// 4. Test create token untuk user guru pertama
echo "4️⃣  Test Create Token:\n";
$guruUser = \App\Models\User::whereIn('role', ['guru', 'Guru'])->first();
if ($guruUser && $guruUser->guru) {
    echo "   ✅ User ditemukan: {$guruUser->name}\n";
    echo "   ✅ Guru ID: {$guruUser->guru->id}\n";
    
    // Buat token
    $token = $guruUser->createToken('test-token')->plainTextToken;
    echo "\n   🔑 Token untuk testing:\n";
    echo "   {$token}\n\n";
    
    echo "   📋 Copy-paste ke browser console:\n";
    echo "   sessionStorage.setItem('token', '{$token}')\n";
    echo "   sessionStorage.setItem('user', '" . json_encode([
        'id' => $guruUser->id,
        'name' => $guruUser->name,
        'email' => $guruUser->email,
        'role' => strtolower($guruUser->role),
    ]) . "')\n";
    echo "   window.location.href = '/dashboard/guru/tugas'\n\n";
} else {
    echo "   ❌ Tidak ada user guru dengan data lengkap!\n\n";
}

// 5. Cek tabel kelas
echo "5️⃣  Cek Tabel Kelas:\n";
$kelas = \App\Models\Kelas::count();
echo "   Jumlah kelas: {$kelas}\n";
if ($kelas == 0) {
    echo "   ⚠️  Tidak ada data kelas! Guru tidak bisa buat tugas tanpa kelas.\n";
    echo "   Solusi: php artisan db:seed --class=KelasSeeder\n";
}
echo "\n";

// 6. Cek tabel mapels (subjek)
echo "6️⃣  Cek Tabel Mapels:\n";
$mapels = \DB::table('mapels')->count();
echo "   Jumlah mata pelajaran: {$mapels}\n";
if ($mapels == 0) {
    echo "   ⚠️  Tidak ada data mapel! Guru tidak bisa buat tugas tanpa mapel.\n";
}
echo "\n";

// 7. Summary
echo "=" . str_repeat("=", 60) . "\n";
echo "SUMMARY:\n";
echo "=" . str_repeat("=", 60) . "\n";

$issues = [];

if ($users->isEmpty()) {
    $issues[] = "Tidak ada user guru";
}
if ($gurus->isEmpty()) {
    $issues[] = "Tabel gurus kosong";
}
if ($guruKapital > 0 && $guruLower == 0) {
    $issues[] = "Role menggunakan kapital (perlu lowercase)";
}
if ($kelas == 0) {
    $issues[] = "Tidak ada data kelas";
}
if ($mapels == 0) {
    $issues[] = "Tidak ada data mapel";
}

if (empty($issues)) {
    echo "✅ SEMUA SETUP SUDAH BENAR!\n";
    echo "   Sistem tugas siap digunakan.\n\n";
} else {
    echo "⚠️  ADA MASALAH:\n";
    foreach ($issues as $issue) {
        echo "   - {$issue}\n";
    }
    echo "\n";
    echo "💡 Solusi Cepat:\n";
    echo "   php artisan migrate:fresh --seed\n\n";
}

echo "Selesai!\n";
