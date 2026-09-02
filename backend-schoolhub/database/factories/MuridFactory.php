<?php

namespace Database\Factories;

use App\Models\Murid;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Murid>
 */
class MuridFactory extends Factory
{
    protected $model = Murid::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = $this->faker->randomElement(['L', 'P']);
        $nama = $gender === 'L' 
            ? $this->faker->firstNameMale() 
            : $this->faker->firstNameFemale();
        $nama_lengkap = $nama . ' ' . $this->faker->lastName();

        return [
            'user_id' => User::factory(),
            'kelas_id' => Kelas::factory(),
            'nis' => $this->faker->unique()->numerify('##########'),
            'Nama_lengkap_murid' => $nama_lengkap,
            'gender' => $gender,
            'tanggal_lahir' => $this->faker->date('Y-m-d', '-15 years'),
            'alamat' => $this->faker->address(),
            'nomor_telepon' => $this->faker->phoneNumber(),
            'nama_orangtua' => $this->faker->name(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Murid $murid) {
            // Logic after making (before saving)
        })->afterCreating(function (Murid $murid) {
            // Update user with Murid role
            if ($murid->user) {
                $murid->user->update(['role' => 'Murid']);
            }
        });
    }
}
