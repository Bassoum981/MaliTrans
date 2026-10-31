<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Ligne;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PrerequisDBSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // 2. Lignes de transport de base
        Ligne::firstOrCreate(
            ['ville_depart' => 'Bamako', 'ville_arrivee' => 'Sikasso'],
            [
                'prix_base' => 7500,
                'distance_km' => 380,
            ]
        );

        Ligne::firstOrCreate(
            ['ville_depart' => 'Bamako', 'ville_arrivee' => 'Mopti'],
            [
                'prix_base' => 12000,
                'distance_km' => 650,
            ]
        );

        // 3. Flotte de bus de base
        Bus::firstOrCreate(
            ['immatriculation' => 'AB-123-MD'],
            [
                'capacite' => 50,
                'statut' => 'EN_SERVICE',
            ]
        );

        Bus::firstOrCreate(
            ['immatriculation' => 'CD-456-MD'],
            [
                'capacite' => 30,
                'statut' => 'EN_SERVICE',
            ]
        );
    }
}
