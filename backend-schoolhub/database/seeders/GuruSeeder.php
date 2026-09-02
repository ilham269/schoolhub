<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guruData = [
            [
                'nama' => 'Ahmad Fauzi, S.Pd',
                'email' => 'ahmad.fauzi@schoolhub.com',
                'nip' => '1987654321000001',
                'gender' => 'L',
                'tanggal_lahir' => '1985-03-15',
                'alamat' => 'Jl. Pendidikan No. 10, Jakarta',
                'nomor_telepon' => '081234567890',
            ],
            [
                'nama' => 'Siti Nurhaliza, S.Pd',
                'email' => 'siti.nurhaliza@schoolhub.com',
                'nip' => '1987654321000002',
                'gender' => 'P',
                'tanggal_lahir' => '1987-07-20',
                'alamat' => 'Jl. Guru No. 5, Jakarta',
                'nomor_telepon' => '081234567891',
            ],
            [
                'nama' => 'Budi Santoso, S.Pd',
                'email' => 'budi.santoso@schoolhub.com',
                'nip' => '1987654321000003',
                'gender' => 'L',
                'tanggal_lahir' => '1983-11-10',
                'alamat' => 'Jl. Pendidik No. 15, Jakarta',
                'nomor_telepon' => '081234567892',
            ],
            [
                'nama' => 'Dewi Lestari, S.Pd',
                'email' => 'dewi.lestari@schoolhub.com',
                'nip' => '1987654321000004',
                'gender' => 'P',
                'tanggal_lahir' => '1990-05-25',
                'alamat' => 'Jl. Cendekia No. 20, Jakarta',
                'nomor_telepon' => '081234567893',
            ],
            [
                'nama' => 'Eko Prasetyo, S.Pd',
                'email' => 'eko.prasetyo@schoolhub.com',
                'nip' => '1987654321000005',
                'gender' => 'L',
                'tanggal_lahir' => '1988-09-12',
                'alamat' => 'Jl. Ilmu No. 8, Jakarta',
                'nomor_telepon' => '081234567894',
            ],
        ];

        foreach ($guruData as $data) {
            // Create user
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'Guru',
                'is_active' => true,
            ]);

            // Create guru profile
            Guru::create([
                'user_id' => $user->id,
                'nip' => $data['nip'],
                'nama_lengkap_guru' => $data['nama'],
                'gender' => $data['gender'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'alamat' => $data['alamat'],
                'nomor_telepon' => $data['nomor_telepon'],
            ]);
        }

        $this->command->info('Guru users created successfully!');
    }
}
