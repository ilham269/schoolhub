<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $angkatan = now()->year;

        $kelasData = [

            // =========================
            // KELAS X
            // =========================
            [
                'name' => 'X RPL 1',
                'kelas' => 'X',
                'jurusan' => 'RPL',
                'angkatan' => $angkatan,
                'wali_kelas' => null,
            ],
            [
                'name' => 'X RPL 2',
                'kelas' => 'X',
                'jurusan' => 'RPL',
                'angkatan' => $angkatan,
                'wali_kelas' => null,
            ],
            [
                'name' => 'X TKR 1',
                'kelas' => 'X',
                'jurusan' => 'TKR',
                'angkatan' => $angkatan,
                'wali_kelas' => null,
            ],
            [
                'name' => 'X TKR 2',
                'kelas' => 'X',
                'jurusan' => 'TKR',
                'angkatan' => $angkatan,
                'wali_kelas' => null,
            ],
            [
                'name' => 'X TSM 1',
                'kelas' => 'X',
                'jurusan' => 'TSM',
                'angkatan' => $angkatan,
                'wali_kelas' => null,
            ],
            [
                'name' => 'X TSM 2',
                'kelas' => 'X',
                'jurusan' => 'TSM',
                'angkatan' => $angkatan,
                'wali_kelas' => null,
            ],

            // =========================
            // KELAS XI
            // =========================
            [
                'name' => 'XI RPL 1',
                'kelas' => 'XI',
                'jurusan' => 'RPL',
                'angkatan' => $angkatan - 1,
                'wali_kelas' => null,
            ],
            [
                'name' => 'XI RPL 2',
                'kelas' => 'XI',
                'jurusan' => 'RPL',
                'angkatan' => $angkatan - 1,
                'wali_kelas' => null,
            ],
            [
                'name' => 'XI TKR 1',
                'kelas' => 'XI',
                'jurusan' => 'TKR',
                'angkatan' => $angkatan - 1,
                'wali_kelas' => null,
            ],
            [
                'name' => 'XI TKR 2',
                'kelas' => 'XI',
                'jurusan' => 'TKR',
                'angkatan' => $angkatan - 1,
                'wali_kelas' => null,
            ],
            [
                'name' => 'XI TSM 1',
                'kelas' => 'XI',
                'jurusan' => 'TSM',
                'angkatan' => $angkatan - 1,
                'wali_kelas' => null,
            ],
            [
                'name' => 'XI TSM 2',
                'kelas' => 'XI',
                'jurusan' => 'TSM',
                'angkatan' => $angkatan - 1,
                'wali_kelas' => null,
            ],

            // =========================
            // KELAS XII
            // =========================
            [
                'name' => 'XII RPL 1',
                'kelas' => 'XII',
                'jurusan' => 'RPL',
                'angkatan' => $angkatan - 2,
                'wali_kelas' => null,
            ],
            [
                'name' => 'XII RPL 2',
                'kelas' => 'XII',
                'jurusan' => 'RPL',
                'angkatan' => $angkatan - 2,
                'wali_kelas' => null,
            ],
            [
                'name' => 'XII TKR 1',
                'kelas' => 'XII',
                'jurusan' => 'TKR',
                'angkatan' => $angkatan - 2,
                'wali_kelas' => null,
            ],
            [
                'name' => 'XII TKR 2',
                'kelas' => 'XII',
                'jurusan' => 'TKR',
                'angkatan' => $angkatan - 2,
                'wali_kelas' => null,
            ],
            [
                'name' => 'XII TSM 1',
                'kelas' => 'XII',
                'jurusan' => 'TSM',
                'angkatan' => $angkatan - 2,
                'wali_kelas' => null,
            ],
            [
                'name' => 'XII TSM 2',
                'kelas' => 'XII',
                'jurusan' => 'TSM',
                'angkatan' => $angkatan - 2,
                'wali_kelas' => null,
            ],
        ];

        foreach ($kelasData as $kelas) {
            if (Kelas::where('name', $kelas['name'])->exists()) {
                $this->command->info("Kelas {$kelas['name']} sudah ada, dilewati.");
                continue;
            }

            Kelas::create($kelas);
        }

        $this->command->info(
            count($kelasData) . ' kelas berhasil dibuat!'
        );
    }
}
