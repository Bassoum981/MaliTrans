<?php

namespace Database\Seeders;

use App\Models\Billet;
use App\Models\Bus;
use App\Models\Ligne;
use App\Models\PositionGps;
use App\Models\Trajet;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            PrerequisDBSeeder::class,
        ]);

        $guichetiers = collect([
            User::firstOrCreate(
                ['email' => 'guichetier@malitrans.ml'],
                [
                    'name' => 'Guichetier Démonstration',
                    'telephone' => '+22370000001',
                    'password' => Hash::make('password'),
                    'role' => 'guichetier',
                ]
            ),
        ])->merge(
            User::factory()
                ->guichetier()
                ->count(2)
                ->create()
        );

        $chauffeurs = User::factory()
            ->chauffeur()
            ->count(6)
            ->create();

        $buses = Bus::factory()
            ->count(8)
            ->create();

        $lignes = Ligne::factory()
            ->count(8)
            ->create();

        $trajets = collect();

        foreach (range(0, 15) as $index) {
            $departure = Carbon::today()
                ->addDays(intdiv($index, 3))
                ->setTime(6 + (($index % 3) * 4), 0);

            $trajets->push(Trajet::factory()->create([
                'bus_id' => $buses[$index % $buses->count()]->id,
                'ligne_id' => $lignes[$index % $lignes->count()]->id,
                'chauffeur_id' => $chauffeurs[$index % $chauffeurs->count()]->id,
                'heure_depart_prevue' => $departure,
            ]));
        }

        foreach ($trajets->take(8) as $index => $trajet) {
            $trajet->loadMissing(['bus', 'ligne']);

            Billet::factory()->create([
                'trajet_id' => $trajet->id,
                'vendu_par' => $guichetiers[$index % $guichetiers->count()]->id,
                'numero_siege' => $index + 1,
                'prix' => $trajet->ligne->prix_base,
            ]);
        }

        foreach ($trajets->take(4) as $trajet) {
            PositionGps::factory()->count(3)->create([
                'trajet_id' => $trajet->id,
            ]);
        }
    }
}
