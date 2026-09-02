<?php

namespace Database\Factories;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Karyawan>
 */
class KaryawanFactory extends Factory
{
    protected $model = Karyawan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bagian = $this->faker->randomElement([
            'Tata Usaha',
            'Perpustakaan',
            'Kebersihan',
            'Keamanan',
            'IT Support',
            'Administrasi'
        ]);

        return [
            'user_id' => User::factory(),
            'nip' => $this->faker->unique()->numerify('####################'),
            'nama_lengkap_karyawan' => $this->faker->name(),
            'bagian' => $bagian,
            'nomor_telepon' => $this->faker->phoneNumber(),
            'alamat' => $this->faker->address(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Karyawan $karyawan) {
            // Logic after making (before saving)
        })->afterCreating(function (Karyawan $karyawan) {
            // Update user with Karyawan role
            if ($karyawan->user) {
                $karyawan->user->update(['role' => 'Karyawan']);
            }
        });
    }
}
