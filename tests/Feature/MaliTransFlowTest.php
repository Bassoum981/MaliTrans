<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Bus;
use App\Models\Ligne;
use App\Models\Trajet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaliTransFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_guichetier_peut_lister_les_trajets_disponibles(): void
    {
        $guichetier = User::factory()->create(['role' => 'guichetier']);
        $ligne = Ligne::create([
            'ville_depart' => 'Bamako',
            'ville_arrivee' => 'Sikasso',
            'prix_base' => 8000
        ]);
        $bus = Bus::create([
            'immatriculation' => 'AB-123-MD',
            'capacite' => 50,
            'statut' => 'EN_SERVICE'
        ]);
        Trajet::create([
            'bus_id' => $bus->id,
            'ligne_id' => $ligne->id,
            'chauffeur_id' => User::factory()->create(['role' => 'chauffeur'])->id,
            'heure_depart_prevue' => now()->addDay(),
            'statut' => 'PLANIFIE'
        ]);

        // Génération d'un token Sanctum valide pour le test API
        $token = $guichetier->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/guichet/trajets');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data'])
            ->assertJsonCount(1, 'data');
    }

    public function test_un_guichetier_peut_emettre_un_billet_et_un_chauffeur_peut_le_valider(): void
    {
        $guichetier = User::factory()->create(['role' => 'guichetier']);
        $chauffeur = User::factory()->create(['role' => 'chauffeur']);

        $ligne = Ligne::create([
            'ville_depart' => 'Bamako',
            'ville_arrivee' => 'Sikasso',
            'prix_base' => 8000
        ]);
        $bus = Bus::create([
            'immatriculation' => 'CD-456-MD',
            'capacite' => 2,
            'statut' => 'EN_SERVICE'
        ]);
        $trajet = Trajet::create([
            'bus_id' => $bus->id,
            'ligne_id' => $ligne->id,
            'chauffeur_id' => $chauffeur->id,
            'heure_depart_prevue' => now()->addDay(),
            'statut' => 'PLANIFIE'
        ]);

        // 1. Émission du billet par le guichetier (via token)
        $tokenGuichetier = $guichetier->createToken('test-token')->plainTextToken;

        $responseVente = $this->withToken($tokenGuichetier)->postJson("/api/v1/guichet/trajets/{$trajet->id}/billets", [
            'nom_passager' => 'Moussa Diarra',
            'telephone_passager' => '+22360000000',
            'numero_siege' => 1,
        ]);

        $responseVente->assertStatus(201)
            ->assertJsonPath('success', true);

        $qrHash = $responseVente->json('data.code_qr_hash');
        $this->assertNotEmpty($qrHash);

        // 2. Validation du billet par le chauffeur via Scan QR Code (via token)
        $tokenChauffeur = $chauffeur->createToken('test-token')->plainTextToken;

        $responseValidation = $this->withToken($tokenChauffeur)->postJson("/api/v1/driver/trajets/{$trajet->id}/valider-billet", [
            'code_qr_hash' => $qrHash,
        ]);

        $responseValidation->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.statut', 'VALIDE');

        // 3. Tentative de double validation (Doit échouer avec 422)
        $responseDoubleValidation = $this->withToken($tokenChauffeur)->postJson("/api/v1/driver/trajets/{$trajet->id}/valider-billet", [
            'code_qr_hash' => $qrHash,
        ]);

        $responseDoubleValidation->assertStatus(422);
    }

    public function test_un_admin_peut_creer_un_trajet_puis_cycle_vente_et_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guichetier = User::factory()->create(['role' => 'guichetier']);
        $chauffeur = User::factory()->create(['role' => 'chauffeur']);

        $ligne = Ligne::create([
            'ville_depart' => 'Bamako',
            'ville_arrivee' => 'Mopti',
            'prix_base' => 12000
        ]);
        $bus = Bus::create([
            'immatriculation' => 'ML-999-MD',
            'capacite' => 40,
            'statut' => 'EN_SERVICE'
        ]);

        // 1. L'admin crée / planifie le trajet
        $tokenAdmin = $admin->createToken('test-token')->plainTextToken;

        $responseTrajet = $this->withToken($tokenAdmin)->postJson('/api/v1/admin/trajets', [
            'ligne_id' => $ligne->id,
            'bus_id' => $bus->id,
            'chauffeur_id' => $chauffeur->id,
            'heure_depart_prevue' => now()->addDays(2)->toDateTimeString(),
            'prix_base' => 12000,
        ]);

        $responseTrajet->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.statut', 'PLANIFIE');

        $trajetId = $responseTrajet->json('data.id');

        // 2. Le guichetier vend un billet sur ce nouveau trajet
        $tokenGuichetier = $guichetier->createToken('test-token')->plainTextToken;

        $responseVente = $this->withToken($tokenGuichetier)->postJson("/api/v1/guichet/trajets/{$trajetId}/billets", [
            'nom_passager' => 'Fatou Diallo',
            'telephone_passager' => '+22370112233',
            'numero_siege' => 5,
        ]);

        $responseVente->assertStatus(201);
        $qrHash = $responseVente->json('data.code_qr_hash');

        // 3. Le chauffeur valide le billet
        $tokenChauffeur = $chauffeur->createToken('test-token')->plainTextToken;

        $responseValidation = $this->withToken($tokenChauffeur)->postJson("/api/v1/driver/trajets/{$trajetId}/valider-billet", [
            'code_qr_hash' => $qrHash,
        ]);

        $responseValidation->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_un_admin_peut_creer_bus_et_ligne_puis_planifier_trajet(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chauffeur = User::factory()->create(['role' => 'chauffeur']);
        $tokenAdmin = $admin->createToken('test-token')->plainTextToken;

        // 1. Création d'une ligne par l'admin
        $responseLigne = $this->withToken($tokenAdmin)->postJson('/api/v1/admin/lignes', [
            'ville_depart' => 'Bamako',
            'ville_arrivee' => 'Kayes',
            'prix_base' => 10000,
            'distance_km' => 500,
        ]);

        $responseLigne->assertStatus(201)->assertJsonPath('success', true);
        $ligneId = $responseLigne->json('data.id');

        // 2. Création d'un bus par l'admin
        $responseBus = $this->withToken($tokenAdmin)->postJson('/api/v1/admin/buses', [
            'immatriculation' => 'ML-555-MD',
            'capacite' => 45,
        ]);

        $responseBus->assertStatus(201)->assertJsonPath('success', true);
        $busId = $responseBus->json('data.id');

        // 3. Planification du trajet avec ces nouveaux IDs
        $responseTrajet = $this->withToken($tokenAdmin)->postJson('/api/v1/admin/trajets', [
            'ligne_id' => $ligneId,
            'bus_id' => $busId,
            'chauffeur_id' => $chauffeur->id,
            'heure_depart_prevue' => now()->addDays(3)->toDateTimeString(),
            'prix_base' => 10000,
        ]);

        $responseTrajet->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.statut', 'PLANIFIE');
    }


}
