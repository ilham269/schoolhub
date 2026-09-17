<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MapelSeeder extends Seeder
{
    public function run(): void
    {
        $mapels = [
            ['nama_mapel' => 'Pemrograman Web', 'kode_mapel' => 'PWB', 'jumlah_jam' => 4, 'kkm' => 75],
            ['nama_mapel' => 'Basis Data', 'kode_mapel' => 'BD', 'jumlah_jam' => 4, 'kkm' => 75],
            ['nama_mapel' => 'Pemrograman Berorientasi Objek', 'kode_mapel' => 'PBO', 'jumlah_jam' => 4, 'kkm' => 75],
            ['nama_mapel' => 'Matematika', 'kode_mapel' => 'MTK', 'jumlah_jam' => 3, 'kkm' => 70],
            ['nama_mapel' => 'Bahasa Indonesia', 'kode_mapel' => 'BIND', 'jumlah_jam' => 2, 'kkm' => 75],
            ['nama_mapel' => 'Bahasa Inggris', 'kode_mapel' => 'BING', 'jumlah_jam' => 2, 'kkm' => 70],
            ['nama_mapel' => 'Pendidikan Agama', 'kode_mapel' => 'PAI', 'jumlah_jam' => 2, 'kkm' => 75],
            ['nama_mapel' => 'PJOK', 'kode_mapel' => 'PJOK', 'jumlah_jam' => 2, 'kkm' => 75],
        ];

        foreach ($mapels as $mapel) {
            if (!DB::table('mapels')->where('kode_mapel', $mapel['kode_mapel'])->exists()) {
                DB::table('mapels')->insert(array_merge($mapel, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $this->command->info('✅ Mapel seeded successfully!');
    }
}
