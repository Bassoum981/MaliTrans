<?php

namespace Database\Factories;

use App\Models\Ligne;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ligne>
 */
class LigneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ville_depart' => fake()->randomElement(['Bamako', 'Sikasso', 'Mopti', 'Segou', 'Kayes']),
            'ville_arrivee' => fake()->randomElement(['Bamako', 'Sikasso', 'Mopti', 'Segou', 'Kayes']),
            'distance_km' => fake()->randomFloat(2, 100, 800),
            'prix_base' => fake()->randomElement([5000, 7500, 10000, 12000, 15000]),
        ];
    }
}
