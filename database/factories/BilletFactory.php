<?php

namespace Database\Factories;

use App\Models\Billet;
use App\Models\Trajet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Billet>
 */
class BilletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code_qr_hash' => (string) Str::uuid(),
            'trajet_id' => Trajet::factory(),
            'nom_passager' => fake()->name(),
            'telephone_passager' => fake()->numerify('+223########'),
            'numero_siege' => fake()->numberBetween(1, 50),
            'type_vente' => 'GUICHET',
            'vendu_par' => User::factory()->guichetier(),
            'prix' => fake()->randomElement([5000, 7500, 10000, 12000, 15000]),
            'statut' => 'PAYE',
            'date_validation' => null,
        ];
    }

    public function valide(): static
    {
        return $this->state(fn (array $attributes): array => [
            'statut' => 'VALIDE',
            'date_validation' => now(),
        ]);
    }
}
