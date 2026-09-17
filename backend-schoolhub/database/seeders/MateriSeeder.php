<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MateriSeeder extends Seeder
{
    public function run(): void
    {
        $mapels   = DB::table('mapels')->pluck('id', 'kode_mapel')->toArray();
        $kelasIds = DB::table('kelas')->orderBy('id')->pluck('id')->toArray();
        $gurus    = DB::table('gurus')->orderBy('id')->pluck('id')->toArray();

        if (empty($mapels)) {
            $this->command->warn('⚠️  Mapel tidak ditemukan. Jalankan MapelSeeder dulu!');
            return;
        }
        if (empty($kelasIds)) {
            $this->command->warn('⚠️  Kelas tidak ditemukan. Jalankan KelasSeeder dulu!');
            return;
        }
        if (empty($gurus)) {
            $this->command->warn('⚠️  Guru tidak ditemukan. Jalankan GuruSeeder dulu!');
            return;
        }

        // Helper — ambil ID kelas berdasarkan urutan, fallback ke index 0
        $kelas = fn (int $idx) => $kelasIds[$idx] ?? $kelasIds[0];
        $guru  = fn (int $idx) => $gurus[$idx]  ?? $gurus[0];

        // Setiap materi menggunakan kolom sesuai migration:
        // kelas_id, guru_id, mapel_id, judul, deskripsi, konten,
        // file_path, link, tanggal_upload, is_published
        $rows = [
            // ── Pemrograman Web ───────────────────────────────────────────
            [
                'kelas_id'      => $kelas(0),
                'guru_id'       => $guru(0),
                'mapel_id'      => $mapels['PWB'] ?? null,
                'judul'         => 'Dasar HTML & CSS',
                'deskripsi'     => 'Tag HTML, selector CSS, dan layout dasar untuk membangun halaman web pertama.',
                'link'          => 'https://developer.mozilla.org/id/docs/Learn/Getting_started_with_the_web',
                'tanggal_upload' => now()->subDays(30)->toDateString(),
                'is_published'  => true,
            ],
            [
                'kelas_id'      => $kelas(0),
                'guru_id'       => $guru(0),
                'mapel_id'      => $mapels['PWB'] ?? null,
                'judul'         => 'JavaScript Fundamental',
                'deskripsi'     => 'Variabel, fungsi, event, dan DOM manipulation dengan JavaScript vanilla.',
                'link'          => 'https://javascript.info',
                'tanggal_upload' => now()->subDays(25)->toDateString(),
                'is_published'  => true,
            ],
            [
                'kelas_id'      => $kelas(1) ?? $kelas(0),
                'guru_id'       => $guru(0),
                'mapel_id'      => $mapels['PWB'] ?? null,
                'judul'         => 'Responsive Web Design',
                'deskripsi'     => 'Flexbox, CSS Grid, dan Media Queries untuk tampilan multi-device.',
                'link'          => 'https://web.dev/learn/design',
                'tanggal_upload' => now()->subDays(20)->toDateString(),
                'is_published'  => true,
            ],
            [
                'kelas_id'      => $kelas(1) ?? $kelas(0),
                'guru_id'       => $guru(0),
                'mapel_id'      => $mapels['PWB'] ?? null,
                'judul'         => 'Framework Vue.js',
                'deskripsi'     => 'Pengenalan Vue 3 Composition API untuk membangun SPA.',
                'link'          => 'https://vuejs.org/guide/introduction.html',
                'tanggal_upload' => now()->subDays(15)->toDateString(),
                'is_published'  => true,
            ],

            // ── Basis Data ────────────────────────────────────────────────
            [
                'kelas_id'      => $kelas(0),
                'guru_id'       => $guru(1) ?? $guru(0),
                'mapel_id'      => $mapels['BD'] ?? null,
                'judul'         => 'Pengenalan Database Relasional',
                'deskripsi'     => 'Konsep dasar RDBMS, ERD, normalisasi 1NF–3NF, dan foreign key.',
                'link'          => 'https://www.postgresqltutorial.com',
                'tanggal_upload' => now()->subDays(28)->toDateString(),
                'is_published'  => true,
            ],
            [
                'kelas_id'      => $kelas(0),
                'guru_id'       => $guru(1) ?? $guru(0),
                'mapel_id'      => $mapels['BD'] ?? null,
                'judul'         => 'SQL Query Dasar',
                'deskripsi'     => 'SELECT, INSERT, UPDATE, DELETE — CRUD lengkap dengan latihan interaktif.',
                'link'          => 'https://sqlzoo.net',
                'tanggal_upload' => now()->subDays(22)->toDateString(),
                'is_published'  => true,
            ],
            [
                'kelas_id'      => $kelas(1) ?? $kelas(0),
                'guru_id'       => $guru(1) ?? $guru(0),
                'mapel_id'      => $mapels['BD'] ?? null,
                'judul'         => 'SQL JOIN & Subquery',
                'deskripsi'     => 'INNER, LEFT, RIGHT JOIN dan subquery bersarang untuk query kompleks.',
                'link'          => null,
                'tanggal_upload' => now()->subDays(18)->toDateString(),
                'is_published'  => true,
            ],

            // ── PBO ───────────────────────────────────────────────────────
            [
                'kelas_id'      => $kelas(6) ?? $kelas(0),   // XI RPL biasanya index 6–7
                'guru_id'       => $guru(2) ?? $guru(0),
                'mapel_id'      => $mapels['PBO'] ?? null,
                'judul'         => 'Konsep OOP',
                'deskripsi'     => 'Class, object, inheritance, polymorphism, dan encapsulation dengan PHP.',
                'link'          => 'https://www.php.net/manual/en/language.oop5.php',
                'tanggal_upload' => now()->subDays(25)->toDateString(),
                'is_published'  => true,
            ],
            [
                'kelas_id'      => $kelas(6) ?? $kelas(0),
                'guru_id'       => $guru(2) ?? $guru(0),
                'mapel_id'      => $mapels['PBO'] ?? null,
                'judul'         => 'Inheritance & Polymorphism',
                'deskripsi'     => 'Pewarisan class dan method overriding untuk code reusability.',
                'link'          => null,
                'tanggal_upload' => now()->subDays(18)->toDateString(),
                'is_published'  => true,
            ],

            // ── Matematika ────────────────────────────────────────────────
            [
                'kelas_id'      => $kelas(2) ?? $kelas(0),   // X TKR
                'guru_id'       => $guru(0),
                'mapel_id'      => $mapels['MTK'] ?? null,
                'judul'         => 'Trigonometri',
                'deskripsi'     => 'Fungsi sin, cos, tan, identitas trigonometri, dan penerapannya.',
                'link'          => null,
                'tanggal_upload' => now()->subDays(20)->toDateString(),
                'is_published'  => true,
            ],
            [
                'kelas_id'      => $kelas(2) ?? $kelas(0),
                'guru_id'       => $guru(0),
                'mapel_id'      => $mapels['MTK'] ?? null,
                'judul'         => 'Aljabar Linear',
                'deskripsi'     => 'Matriks, determinan, dan sistem persamaan linear.',
                'link'          => null,
                'tanggal_upload' => now()->subDays(14)->toDateString(),
                'is_published'  => true,
            ],
        ];

        $count = 0;
        foreach ($rows as $row) {
            if (! $row['mapel_id']) {
                continue; // skip jika mapel tidak ada
            }
            $exists = DB::table('materis')
                ->where('judul', $row['judul'])
                ->where('kelas_id', $row['kelas_id'])
                ->exists();
            if (! $exists) {
                DB::table('materis')->insert(array_merge($row, [
                    'file_path'  => null,
                    'konten'     => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $count++;
            }
        }

        $this->command->info("✅ {$count} materi seeded.");
    }
}
