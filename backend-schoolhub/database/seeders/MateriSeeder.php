<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MateriSeeder extends Seeder
{
    public function run(): void
    {
        // Get mapel IDs
        $pwbId = DB::table('mapels')->where('kode_mapel', 'PWB')->value('id');
        $bdId = DB::table('mapels')->where('kode_mapel', 'BD')->value('id');
        $pboId = DB::table('mapels')->where('kode_mapel', 'PBO')->value('id');

        if (!$pwbId || !$bdId || !$pboId) {
            $this->command->warn('⚠️  Mapel tidak ditemukan. Jalankan MapelSeeder dulu!');
            return;
        }

        // Get guru IDs
        $guruId = DB::table('gurus')->first()->id ?? null;

        if (!$guruId) {
            $this->command->warn('⚠️  Guru tidak ditemukan. Jalankan GuruSeeder dulu!');
            return;
        }

        $materis = [
            [
                'mapel_id' => $pwbId,
                'guru_id' => $guruId,
                'judul' => 'Dasar HTML & CSS',
                'deskripsi' => 'Mempelajari dasar-dasar HTML dan CSS untuk membuat website',
                'file_path' => null,
            ],
            [
                'mapel_id' => $pwbId,
                'guru_id' => $guruId,
                'judul' => 'JavaScript Fundamental',
                'deskripsi' => 'Belajar JavaScript dari dasar: variabel, fungsi, dan DOM manipulation',
                'file_path' => null,
            ],
            [
                'mapel_id' => $bdId,
                'guru_id' => $guruId,
                'judul' => 'Pengenalan Database',
                'deskripsi' => 'Konsep dasar database, ERD, dan normalisasi',
                'file_path' => null,
            ],
            [
                'mapel_id' => $bdId,
                'guru_id' => $guruId,
                'judul' => 'SQL Query',
                'deskripsi' => 'Mempelajari SQL: SELECT, INSERT, UPDATE, DELETE, JOIN',
                'file_path' => null,
            ],
            [
                'mapel_id' => $pboId,
                'guru_id' => $guruId,
                'judul' => 'Konsep OOP',
                'deskripsi' => 'Class, Object, Inheritance, Polymorphism, Encapsulation',
                'file_path' => null,
            ],
        ];

        foreach ($materis as $materi) {
            if (!DB::table('materis')->where('judul', $materi['judul'])->exists()) {
                DB::table('materis')->insert(array_merge($materi, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $this->command->info('✅ Materi seeded successfully!');
    }
}
