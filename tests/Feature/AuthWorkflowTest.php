<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
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

    public function test_un_guichetier_est_redirige_vers_son_espace_de_vente(): void
    {
        $guichetier = User::factory()->create([
            'email' => 'guichet@malitrans.ml',
            'password' => Hash::make('password123'),
            'role' => 'guichetier',
        ]);

        $response = $this->postJson('/login', [
            'email' => $guichetier->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('redirect', route('guichet.dashboard'));

        $this->assertAuthenticatedAs($guichetier);
        $this->get(route('guichet.dashboard'))->assertOk();
    }

    public function test_un_administrateur_connecte_par_session_peut_creer_un_compte_guichetier(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@malitrans.ml',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->postJson('/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ])->assertOk();

        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'Guichet Session',
            'email' => 'session.guichet@malitrans.ml',
            'telephone' => '+22360000003',
            'password' => 'password123',
            'role' => 'guichetier',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'email' => 'session.guichet@malitrans.ml',
            'role' => 'guichetier',
        ]);
    }

    public function test_la_creation_depuis_la_session_web_ne_depend_pas_du_domaine_stateful_de_sanctum(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->withoutMiddleware(EnsureFrontendRequestsAreStateful::class)
            ->actingAs($admin)
            ->postJson('/api/v1/admin/users', [
                'name' => 'Guichet Domaine',
                'email' => 'domaine.guichet@malitrans.ml',
                'telephone' => '+22360000004',
                'password' => 'password123',
                'role' => 'guichetier',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('users', ['email' => 'domaine.guichet@malitrans.ml']);
    }

    public function test_une_session_guichetier_peut_utiliser_l_api_de_vente(): void
    {
        $guichetier = User::factory()->create(['role' => 'guichetier']);

        $this->actingAs($guichetier)
            ->getJson('/api/v1/guichet/trajets')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
