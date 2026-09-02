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
            // Kelas X
            ['name' => 'X RPL 1', 'kelas' => 'X', 'jurusan' => 'RPL', 'angkatan' => $angkatan],
            ['name' => 'X RPL 2', 'kelas' => 'X', 'jurusan' => 'RPL', 'angkatan' => $angkatan],
            ['name' => 'X TKR 1', 'kelas' => 'X', 'jurusan' => 'TKR', 'angkatan' => $angkatan],
            ['name' => 'X TKR 2', 'kelas' => 'X', 'jurusan' => 'TKR', 'angkatan' => $angkatan],
            ['name' => 'X TSM 1', 'kelas' => 'X', 'jurusan' => 'TSM', 'angkatan' => $angkatan],
            ['name' => 'X TSM 2', 'kelas' => 'X', 'jurusan' => 'TSM', 'angkatan' => $angkatan],
            
            // Kelas XI
            ['name' => 'XI RPL 1', 'kelas' => 'XI', 'jurusan' => 'RPL', 'angkatan' => $angkatan - 1],
            ['name' => 'XI RPL 2', 'kelas' => 'XI', 'jurusan' => 'RPL', 'angkatan' => $angkatan - 1],
            ['name' => 'XI TKR 1', 'kelas' => 'XI', 'jurusan' => 'TKR', 'angkatan' => $angkatan - 1],
            ['name' => 'XI TKR 2', 'kelas' => 'XI', 'jurusan' => 'TKR', 'angkatan' => $angkatan - 1],
            ['name' => 'XI TSM 1', 'kelas' => 'XI', 'jurusan' => 'TSM', 'angkatan' => $angkatan - 1],
            ['name' => 'XI TSM 2', 'kelas' => 'XI', 'jurusan' => 'TSM', 'angkatan' => $angkatan - 1],
            
            // Kelas XII
            ['name' => 'XII RPL 1', 'kelas' => 'XII', 'jurusan' => 'RPL', 'angkatan' => $angkatan - 2],
            ['name' => 'XII RPL 2', 'kelas' => 'XII', 'jurusan' => 'RPL', 'angkatan' => $angkatan - 2],
            ['name' => 'XII TKR 1', 'kelas' => 'XII', 'jurusan' => 'TKR', 'angkatan' => $angkatan - 2],
            ['name' => 'XII TKR 2', 'kelas' => 'XII', 'jurusan' => 'TKR', 'angkatan' => $angkatan - 2],
            ['name' => 'XII TSM 1', 'kelas' => 'XII', 'jurusan' => 'TSM', 'angkatan' => $angkatan - 2],
            ['name' => 'XII TSM 2', 'kelas' => 'XII', 'jurusan' => 'TSM', 'angkatan' => $angkatan - 2],
        ];

        foreach ($kelasData as $kelas) {
            Kelas::create($kelas);
        }

        $this->command->info('Kelas created successfully!');
    }
}
