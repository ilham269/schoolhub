<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TugasDummySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding Tugas & Nilai...');

        // Get necessary IDs
        $gurus = DB::table('gurus')->limit(3)->pluck('id')->toArray();
        $kelas = DB::table('kelas')->whereIn('id', [1, 2, 3, 7, 8])->pluck('id')->toArray();
        $mapels = DB::table('mapels')->pluck('id', 'kode_mapel')->toArray();
        $materis = DB::table('materis')->pluck('id', 'judul')->toArray();

        if (empty($gurus) || empty($kelas) || empty($mapels)) {
            $this->command->error('❌ Data guru, kelas, atau mapel tidak ditemukan!');
            $this->command->warn('   Jalankan seeder: GuruSeeder, KelasSeeder, MapelSeeder');
            return;
        }

        $this->command->info('   Membuat tugas...');

        // Tugas untuk berbagai mapel dan kelas
        $tugasList = [
            // Pemrograman Web
            [
                'guru_id' => $gurus[0] ?? $gurus[0],
                'kelas_id' => $kelas[0] ?? 1, // X RPL 1
                'mapel_id' => $mapels['PWB'] ?? 1,
                'materi_id' => $materis['Dasar HTML & CSS'] ?? null,
                'judul' => 'Membuat Halaman Profil HTML',
                'deskripsi' => 'Buat halaman profil pribadi menggunakan HTML dan CSS dengan elemen: header, foto, bio, dan kontak.',
                'instruksi' => 'Kumpulkan dalam format .zip berisi index.html dan style.css',
                'tanggal_dibuat' => Carbon::now()->subDays(10),
                'deadline' => Carbon::now()->addDays(5),
                'nilai_maksimal' => 100,
                'is_active' => true,
            ],
            [
                'guru_id' => $gurus[0] ?? $gurus[0],
                'kelas_id' => $kelas[0] ?? 1,
                'mapel_id' => $mapels['PWB'] ?? 1,
                'materi_id' => $materis['JavaScript Fundamental'] ?? null,
                'judul' => 'Kalkulator Sederhana dengan JavaScript',
                'deskripsi' => 'Membuat kalkulator yang bisa melakukan operasi +, -, *, / menggunakan JavaScript.',
                'instruksi' => 'Upload file HTML, CSS, dan JS. Pastikan kalkulator berfungsi dengan baik.',
                'tanggal_dibuat' => Carbon::now()->subDays(15),
                'deadline' => Carbon::now()->subDays(2), // Sudah lewat deadline
                'nilai_maksimal' => 100,
                'is_active' => false,
            ],
            [
                'guru_id' => $gurus[0] ?? $gurus[0],
                'kelas_id' => $kelas[1] ?? 2, // X RPL 2
                'mapel_id' => $mapels['PWB'] ?? 1,
                'materi_id' => $materis['Responsive Web Design'] ?? null,
                'judul' => 'Website Responsive Portfolio',
                'deskripsi' => 'Buat website portfolio yang responsive untuk desktop, tablet, dan mobile.',
                'instruksi' => 'Gunakan Flexbox atau Grid. Test di berbagai ukuran layar.',
                'tanggal_dibuat' => Carbon::now()->subDays(5),
                'deadline' => Carbon::now()->addDays(10),
                'nilai_maksimal' => 100,
                'is_active' => true,
            ],

            // Basis Data
            [
                'guru_id' => $gurus[1] ?? $gurus[0],
                'kelas_id' => $kelas[0] ?? 1,
                'mapel_id' => $mapels['BD'] ?? 2,
                'materi_id' => $materis['Pengenalan Database'] ?? null,
                'judul' => 'ERD Sistem Perpustakaan',
                'deskripsi' => 'Rancang ERD (Entity Relationship Diagram) untuk sistem informasi perpustakaan sekolah.',
                'instruksi' => 'Gunakan draw.io atau tools lain. Ekspor sebagai PNG. Sertakan penjelasan singkat.',
                'tanggal_dibuat' => Carbon::now()->subDays(12),
                'deadline' => Carbon::now()->addDays(3),
                'nilai_maksimal' => 100,
                'is_active' => true,
            ],
            [
                'guru_id' => $gurus[1] ?? $gurus[0],
                'kelas_id' => $kelas[0] ?? 1,
                'mapel_id' => $mapels['BD'] ?? 2,
                'materi_id' => $materis['SQL Query Dasar'] ?? null,
                'judul' => 'Latihan SQL Query',
                'deskripsi' => 'Kerjakan 10 soal SQL query yang sudah disediakan di LMS.',
                'instruksi' => 'Tulis query dalam file .sql dan test di MySQL/PostgreSQL.',
                'tanggal_dibuat' => Carbon::now()->subDays(20),
                'deadline' => Carbon::now()->subDays(5), // Sudah lewat
                'nilai_maksimal' => 100,
                'is_active' => false,
            ],

            // PBO
            [
                'guru_id' => $gurus[2] ?? $gurus[0],
                'kelas_id' => $kelas[4] ?? 7, // XI RPL 1
                'mapel_id' => $mapels['PBO'] ?? 3,
                'materi_id' => $materis['Konsep OOP'] ?? null,
                'judul' => 'Implementasi Class di PHP',
                'deskripsi' => 'Buat class Mobil dan Sepeda Motor dengan property dan method yang sesuai.',
                'instruksi' => 'Upload file .php dengan implementasi class. Sertakan contoh penggunaan.',
                'tanggal_dibuat' => Carbon::now()->subDays(8),
                'deadline' => Carbon::now()->addDays(7),
                'nilai_maksimal' => 100,
                'is_active' => true,
            ],

            // Matematika
            [
                'guru_id' => $gurus[0] ?? $gurus[0],
                'kelas_id' => $kelas[2] ?? 3, // X TKR 1
                'mapel_id' => $mapels['MTK'] ?? 4,
                'materi_id' => $materis['Trigonometri'] ?? null,
                'judul' => 'Soal Trigonometri',
                'deskripsi' => 'Kerjakan 15 soal trigonometri yang ada di buku paket halaman 45-50.',
                'instruksi' => 'Tulis jawaban di kertas, foto dengan jelas, upload dalam PDF.',
                'tanggal_dibuat' => Carbon::now()->subDays(7),
                'deadline' => Carbon::now()->addDays(2),
                'nilai_maksimal' => 100,
                'is_active' => true,
            ],
        ];

        $tugasIds = [];
        foreach ($tugasList as $tugas) {
            $tugasId = DB::table('tugas')->insertGetId(array_merge($tugas, [
                'file_path' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            $tugasIds[] = $tugasId;
        }

        $this->command->info('   ✅ ' . count($tugasIds) . ' tugas created');
        $this->command->info('   Membuat pengumpulan tugas & nilai...');

        // Get murids untuk pengumpulan
        $murids = DB::table('murids')->limit(20)->get();

        if ($murids->isEmpty()) {
            $this->command->warn('   ⚠️  Tidak ada data murid. Skip pengumpulan tugas.');
            return;
        }

        $pengumpulanCount = 0;
        foreach ($tugasIds as $index => $tugasId) {
            $tugas = DB::table('tugas')->where('id', $tugasId)->first();
            
            // Filter murid yang kelasnya sama dengan tugas
            $muridsSameClass = $murids->where('kelas_id', $tugas->kelas_id);

            // Beberapa murid mengumpulkan (60-80% dari murid di kelas)
            $submitCount = (int) ($muridsSameClass->count() * (rand(60, 80) / 100));
            $submittingMurids = $muridsSameClass->random(min($submitCount, $muridsSameClass->count()));

            foreach ($submittingMurids as $murid) {
                $isLate = rand(0, 100) < 20; // 20% chance terlambat
                $tanggalKumpul = $isLate 
                    ? Carbon::parse($tugas->deadline)->addDays(rand(1, 3))
                    : Carbon::parse($tugas->deadline)->subDays(rand(1, 5));

                // Generate nilai (75-100, dengan distribusi normal)
                $nilai = rand(75, 100);
                if (rand(0, 100) < 30) { // 30% chance dapat nilai sangat baik (90-100)
                    $nilai = rand(90, 100);
                }

                $status = $isLate ? 'Terlambat' : 
                         ($nilai >= 85 ? 'Dinilai' : 'Dinilai');

                $feedbacks = [
                    'Sangat baik! Pertahankan.',
                    'Bagus, tapi masih bisa lebih baik lagi.',
                    'Cukup baik, perhatikan detail.',
                    'Sudah bagus, tingkatkan pemahaman konsep.',
                    'Kerja yang baik!',
                    'Perlu lebih teliti dalam pengerjaan.',
                ];

                DB::table('pengumpulan_tugas')->insert([
                    'tugas_id' => $tugasId,
                    'murid_id' => $murid->id,
                    'tanggal_pengumpulan' => $tanggalKumpul,
                    'file_path' => 'pengumpulan-tugas/dummy-' . $murid->id . '-' . $tugasId . '.pdf',
                    'link' => null,
                    'catatan' => 'Sudah selesai dikerjakan',
                    'nilai' => $nilai,
                    'feedback' => $feedbacks[array_rand($feedbacks)],
                    'status' => $status,
                    'created_at' => $tanggalKumpul,
                    'updated_at' => now(),
                ]);
                $pengumpulanCount++;
            }
        }

        $this->command->info('   ✅ ' . $pengumpulanCount . ' pengumpulan tugas & nilai created');
        $this->command->info('');
        $this->command->info('✅ Tugas & Nilai seeded successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Tugas: ' . count($tugasIds));
        $this->command->info('   - Pengumpulan dengan nilai: ' . $pengumpulanCount);
    }
}
