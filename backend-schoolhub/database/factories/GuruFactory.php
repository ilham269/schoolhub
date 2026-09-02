<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guru>
 */
class GuruFactory extends Factory
{
    protected $model = Guru::class;

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
        $nama_lengkap = $nama . ' ' . $this->faker->lastName() . ', S.Pd';

        return [
            'user_id' => User::factory(),
            'nip' => $this->faker->unique()->numerify('####################'),
            'nama_lengkap_guru' => $nama_lengkap,
            'gender' => $gender,
            'tanggal_lahir' => $this->faker->date('Y-m-d', '-35 years'),
            'alamat' => $this->faker->address(),
            'nomor_telepon' => $this->faker->phoneNumber(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Guru $guru) {
            // Logic after making (before saving)
        })->afterCreating(function (Guru $guru) {
            // Update user with Guru role
            if ($guru->user) {
                $guru->user->update(['role' => 'Guru']);
            }
        });
    }
}
