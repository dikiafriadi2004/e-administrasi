<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $angkatan = fake()->numberBetween(2018, 2024);
        $nim = $angkatan.fake()->unique()->numerify('###');

        return [
            'user_id' => User::factory()->state(['role' => 'mahasiswa']),
            'nim' => $nim,
            'angkatan' => $angkatan,
        ];
    }
}
