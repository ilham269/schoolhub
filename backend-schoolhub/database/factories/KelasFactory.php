<?php

namespace Database\Factories;

use App\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kelas>
 */
class KelasFactory extends Factory
{
    protected $model = Kelas::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jurusan = $this->faker->randomElement(['RPL', 'TKR', 'TSM']);
        $tingkat = $this->faker->randomElement(['X', 'XI', 'XII']);
        $nomor = $this->faker->numberBetween(1, 2);
        
        return [
            'name' => "{$tingkat} {$jurusan} {$nomor}",
            'kelas' => $tingkat,
            'jurusan' => $jurusan,
            'angkatan' => $this->faker->year(),
        ];
    }

    /**
     * State for RPL class
     */
    public function rpl(): static
    {
        return $this->state(fn (array $attributes) => [
            'jurusan' => 'RPL',
        ]);
    }

    /**
     * State for TKR class
     */
    public function tkr(): static
    {
        return $this->state(fn (array $attributes) => [
            'jurusan' => 'TKR',
        ]);
    }

    /**
     * State for TSM class
     */
    public function tsm(): static
    {
        return $this->state(fn (array $attributes) => [
            'jurusan' => 'TSM',
        ]);
    }

    /**
     * State for specific class level and number
     */
    public function kelas(string $tingkat, int $nomor): static
    {
        return $this->state(function (array $attributes) use ($tingkat, $nomor) {
            $jurusan = $attributes['jurusan'] ?? 'RPL';
            return [
                'name' => "{$tingkat} {$jurusan} {$nomor}",
                'kelas' => $tingkat,
            ];
        });
    }
}
