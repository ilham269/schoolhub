<?php

namespace Database\Seeders;

use App\Models\Murid;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MuridSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $muridData = [
            // Murid untuk XI RPL 1
            [
                'nama' => 'Ahmad Rizki',
                'email' => 'ahmad.rizki@student.schoolhub.com',
                'nis' => '2024001001',
                'gender' => 'L',
                'tanggal_lahir' => '2008-05-12',
                'alamat' => 'Jl. Mawar No. 1, Jakarta',
                'nomor_telepon' => '082111111111',
                'nama_orangtua' => 'Bapak Ahmad',
                'kelas' => 'XI RPL 1',
            ],
            [
                'nama' => 'Budi Setiawan',
                'email' => 'budi.setiawan@student.schoolhub.com',
                'nis' => '2024001002',
                'gender' => 'L',
                'tanggal_lahir' => '2008-08-20',
                'alamat' => 'Jl. Melati No. 5, Jakarta',
                'nomor_telepon' => '082111111112',
                'nama_orangtua' => 'Bapak Budi',
                'kelas' => 'XI RPL 1',
            ],
            [
                'nama' => 'Citra Dewi',
                'email' => 'citra.dewi@student.schoolhub.com',
                'nis' => '2024001003',
                'gender' => 'P',
                'tanggal_lahir' => '2008-03-15',
                'alamat' => 'Jl. Anggrek No. 10, Jakarta',
                'nomor_telepon' => '082111111113',
                'nama_orangtua' => 'Ibu Citra',
                'kelas' => 'XI RPL 1',
            ],
            // Murid untuk XI TKR 1
            [
                'nama' => 'Dedi Gunawan',
                'email' => 'dedi.gunawan@student.schoolhub.com',
                'nis' => '2024002001',
                'gender' => 'L',
                'tanggal_lahir' => '2008-06-18',
                'alamat' => 'Jl. Kenanga No. 2, Jakarta',
                'nomor_telepon' => '082111111114',
                'nama_orangtua' => 'Bapak Dedi',
                'kelas' => 'XI TKR 1',
            ],
            [
                'nama' => 'Eka Pratama',
                'email' => 'eka.pratama@student.schoolhub.com',
                'nis' => '2024002002',
                'gender' => 'L',
                'tanggal_lahir' => '2008-09-22',
                'alamat' => 'Jl. Dahlia No. 7, Jakarta',
                'nomor_telepon' => '082111111115',
                'nama_orangtua' => 'Bapak Eka',
                'kelas' => 'XI TKR 1',
            ],
            // Murid untuk XI TSM 1
            [
                'nama' => 'Fahmi Rizal',
                'email' => 'fahmi.rizal@student.schoolhub.com',
                'nis' => '2024003001',
                'gender' => 'L',
                'tanggal_lahir' => '2008-04-10',
                'alamat' => 'Jl. Sakura No. 3, Jakarta',
                'nomor_telepon' => '082111111116',
                'nama_orangtua' => 'Bapak Fahmi',
                'kelas' => 'XI TSM 1',
            ],
            [
                'nama' => 'Gita Permata',
                'email' => 'gita.permata@student.schoolhub.com',
                'nis' => '2024003002',
                'gender' => 'P',
                'tanggal_lahir' => '2008-11-05',
                'alamat' => 'Jl. Tulip No. 12, Jakarta',
                'nomor_telepon' => '082111111117',
                'nama_orangtua' => 'Ibu Gita',
                'kelas' => 'XI TSM 1',
            ],
        ];

        foreach ($muridData as $data) {
            $kelas = Kelas::where('name', $data['kelas'])->first();
            
            if (!$kelas) {
                $this->command->warn("Kelas {$data['kelas']} not found, skipping {$data['nama']}");
                continue;
            }

            // Create user
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'Murid',
                'is_active' => true,
            ]);

            // Create murid profile
            Murid::create([
                'user_id' => $user->id,
                'kelas_id' => $kelas->id,
                'nis' => $data['nis'],
                'Nama_lengkap_murid' => $data['nama'],
                'gender' => $data['gender'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'alamat' => $data['alamat'],
                'nomor_telepon' => $data['nomor_telepon'],
                'nama_orangtua' => $data['nama_orangtua'],
            ]);
        }

        $this->command->info('Murid users created successfully!');
    }
}
