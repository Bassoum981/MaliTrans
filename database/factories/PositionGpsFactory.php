<?php

namespace Database\Factories;

use App\Models\PositionGps;
use App\Models\Trajet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PositionGps>
 */
class PositionGpsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trajet_id' => Trajet::factory(),
            'latitude' => fake()->latitude(10.1, 17.0),
            'longitude' => fake()->longitude(-12.5, 4.5),
            'recorded_at' => fake()->dateTimeBetween('-2 days', 'now'),
        ];
    }
}
