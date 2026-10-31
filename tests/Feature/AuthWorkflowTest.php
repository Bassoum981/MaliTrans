<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_utilisateur_peut_se_connecter_et_obtenir_un_token()
    {
        // 1. Créer un utilisateur de test
        $user = User::factory()->create([
            'email' => 'guichetier@malitrans.ml',
            'password' => Hash::make('password123'),
            'role' => 'guichetier',
        ]);

        // 2. Tenter la connexion
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'guichetier@malitrans.ml',
            'password' => 'password123',
        ]);

        // 3. Vérifications
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_la_connexion_echoue_avec_de_mauvais_identifiants()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'inexistant@malitrans.ml',
            'password' => 'mauvaismotdepasse',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_un_administrateur_peut_creer_un_compte_employe()
    {
        // 1. Créer un admin et générer son token
        $admin = User::factory()->create(['role' => 'admin']);

        // 2. L'admin enrôle un nouveau guichetier
        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/users', [
            'name' => 'Nouveau Guichetier',
            'email' => 'nouveau.guichet@malitrans.ml',
            'telephone' => '+22360000001',
            'password' => 'password123',
            'role' => 'guichetier',
        ]);

        // 3. Vérifications
        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'nouveau.guichet@malitrans.ml');

        $this->assertDatabaseHas('users', [
            'email' => 'nouveau.guichet@malitrans.ml',
            'role' => 'guichetier',
        ]);
    }

    public function test_un_guichetier_ne_peut_pas_creer_de_compte_employe()
    {
        // 1. Créer un simple guichetier
        $guichetier = User::factory()->create(['role' => 'guichetier']);

        // 2. Tenter de créer un utilisateur (doit échouer avec un 403)
        $response = $this->actingAs($guichetier, 'sanctum')->postJson('/api/v1/admin/users', [
            'name' => 'Tentative Piratage',
            'email' => 'pirate@malitrans.ml',
            'telephone' => '+22360000002',
            'password' => 'password123',
            'role' => 'chauffeur',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }
}
