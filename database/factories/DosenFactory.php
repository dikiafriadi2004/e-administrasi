<?php

namespace Database\Factories;

use App\Models\Dosen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dosen>
 */
class DosenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'nip' => fake()->unique()->numerify('19########0#0###'),
            'kapasitas_maksimal' => null,
        ];
    }

    public function denganKapasitas(int $kapasitas): static
    {
        return $this->state(['kapasitas_maksimal' => $kapasitas]);
    }
}
