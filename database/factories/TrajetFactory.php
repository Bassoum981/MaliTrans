<?php

namespace Database\Factories;

use App\Models\Bus;
use App\Models\Ligne;
use App\Models\Trajet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trajet>
 */
class TrajetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bus_id' => Bus::factory(),
            'ligne_id' => Ligne::factory(),
            'chauffeur_id' => User::factory()->chauffeur(),
            'heure_depart_prevue' => fake()->dateTimeBetween('now', '+14 days'),
            'heure_depart_reelle' => null,
            'heure_arrivee_reelle' => null,
            'statut' => 'PLANIFIE',
        ];
    }

    public function enCours(): static
    {
        return $this->state(fn (array $attributes): array => [
            'statut' => 'EN_COURS',
            'heure_depart_reelle' => now()->subMinutes(30),
        ]);
    }

    public function termine(): static
    {
        return $this->state(fn (array $attributes): array => [
            'statut' => 'TERMINE',
            'heure_depart_reelle' => now()->subHours(6),
            'heure_arrivee_reelle' => now()->subHour(),
        ]);
    }

    public function annule(): static
    {
        return $this->state(fn (array $attributes): array => [
            'statut' => 'ANNULE',
        ]);
    }
}
