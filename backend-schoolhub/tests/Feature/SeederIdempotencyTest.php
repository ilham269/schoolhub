<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Karyawan;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_does_not_duplicate_existing_records(): void
    {
        $this->seed(DatabaseSeeder::class);

        $counts = [
            'users' => User::count(),
            'kelas' => Kelas::count(),
            'guru' => Guru::count(),
            'murid' => Murid::count(),
            'karyawan' => Karyawan::count(),
        ];

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($counts['users'], User::count());
        $this->assertSame($counts['kelas'], Kelas::count());
        $this->assertSame($counts['guru'], Guru::count());
        $this->assertSame($counts['murid'], Murid::count());
        $this->assertSame($counts['karyawan'], Karyawan::count());
    }
}
