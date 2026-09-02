<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $karyawanData = [
            [
                'nama' => 'Rina Kusuma',
                'email' => 'rina.kusuma@schoolhub.com',
                'nip' => '1987654321100001',
                'bagian' => 'Tata Usaha',
                'nomor_telepon' => '081345678901',
                'alamat' => 'Jl. Administrasi No. 1, Jakarta',
            ],
            [
                'nama' => 'Hendra Wijaya',
                'email' => 'hendra.wijaya@schoolhub.com',
                'nip' => '1987654321100002',
                'bagian' => 'Perpustakaan',
                'nomor_telepon' => '081345678902',
                'alamat' => 'Jl. Buku No. 5, Jakarta',
            ],
            [
                'nama' => 'Sari Indah',
                'email' => 'sari.indah@schoolhub.com',
                'nip' => '1987654321100003',
                'bagian' => 'IT Support',
                'nomor_telepon' => '081345678903',
                'alamat' => 'Jl. Teknologi No. 10, Jakarta',
            ],
        ];

        foreach ($karyawanData as $data) {
            // Create user
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'Karyawan',
                'is_active' => true,
            ]);

            // Create karyawan profile
            Karyawan::create([
                'user_id' => $user->id,
                'nip' => $data['nip'],
                'nama_lengkap_karyawan' => $data['nama'],
                'bagian' => $data['bagian'],
                'nomor_telepon' => $data['nomor_telepon'],
                'alamat' => $data['alamat'],
            ]);
        }

        $this->command->info('Karyawan users created successfully!');
    }
}
