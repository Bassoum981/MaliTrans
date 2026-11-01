<?php

namespace Database\Factories;

use App\Models\Bus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bus>
 */
class BusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'immatriculation' => fake()->unique()->bothify('??-###-MD'),
            'capacite' => fake()->randomElement([20, 30, 40, 45, 50, 55]),
            'statut' => 'EN_SERVICE',
        ];
    }

    public function enMaintenance(): static
    {
        return $this->state(fn (array $attributes): array => [
            'statut' => 'EN_MAINTENANCE',
        ]);
    }

    public function horsService(): static
    {
        return $this->state(fn (array $attributes): array => [
            'statut' => 'HORS_SERVICE',
        ]);
    }
}
