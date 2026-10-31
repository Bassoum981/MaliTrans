<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@malitrans.ml'],
            [
                'name' => 'Super Admin MaliTrans',
                'telephone' => '+22394184125',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
    }
}
