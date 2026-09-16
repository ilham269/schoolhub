<?php

namespace Database\Factories;

use App\Models\Subjek;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subjek>
 */
class SubjekFactory extends Factory
{
    protected $model = Subjek::class;

    public function definition(): array
    {
        return [
            'kode_mapel' => strtoupper($this->faker->unique()->bothify('MP###')),
            'nama_mapel' => $this->faker->unique()->words(2, true),
            'deskripsi' => $this->faker->sentence(),
            'jumlah_jam' => 2,
            'kkm' => 75,
            'is_active' => true,
        ];
    }
}
