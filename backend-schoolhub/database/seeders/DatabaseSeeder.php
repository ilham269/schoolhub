<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        
        // Seed in order of dependencies
        $this->call([
            UserSeeder::class,              // Admin
            KelasSeeder::class,             // Kelas (X, XI, XII RPL/TKR/TSM)
            GuruSeeder::class,              // Guru
            MuridSeeder::class,             // Murid
            KaryawanSeeder::class,          // Karyawan
            MapelSeeder::class,             // Mata Pelajaran (NEW!)
            MateriSeeder::class,            // Materi Pembelajaran (NEW!)
            TugasDummySeeder::class,        // Tugas & Nilai Dummy (NEW!)
            NewsSeeder::class,              // Berita publik
            PaymentSettingsSeeder::class,   // Payment & SPP Settings
        ]);

        $this->command->info('');
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📝 Default credentials:');
        $this->command->info('   Email: admin@schoolhub.com (Admin)');
        $this->command->info('   Email: ahmad.fauzi@schoolhub.com (Guru)');
        $this->command->info('   Password: password');
    }
}
