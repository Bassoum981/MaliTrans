<?php

namespace App\Services;

use App\Models\Billet;
use App\Models\Trajet;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketService
{
    /**
     * Générer un nouveau billet pour un trajet donné.
     */
    public function createTicket(Trajet $trajet, array $data): Billet
    {
        return DB::transaction(function () use ($trajet, $data) {
            // Optionnel : Vérifier s'il reste des places dans le bus associé
            $bus = $trajet->bus;
            $ticketsCount = $trajet->billets()->where('statut', '!=', 'ANNULE')->count();

            if ($ticketsCount >= $bus->capacite) {
                throw new Exception("Désolé, ce bus est complet pour ce trajet.");
            }

            // Génération d'un hash unique sécurisé pour le QR Code
            $qrHash = 'MALITRANS-' . strtoupper(Str::random(16)) . '-' . time();

            return Billet::create([
                'code_qr_hash' => $qrHash,
                'trajet_id' => $trajet->id,
                'nom_passager' => $data['nom_passager'],
                'telephone_passager' => $data['telephone_passager'] ?? null,
                'numero_siege' => $data['numero_siege'] ?? ($ticketsCount + 1),
                'type_vente' => $data['type_vente'], // 'WEB' ou 'GUICHET'
                'vendu_par' => $data['vendu_par'] ?? null,
                'prix' => $data['prix'] ?? $trajet->ligne->prix_base,
                'statut' => 'PAYE',
            ]);
        });
    }

    /**
     * Valider un billet par le chauffeur via le scan du QR Code.
     */
    public function validateTicketByQrCode(string $qrHash, int $trajetId): Billet
    {
        $billet = Billet::where('code_qr_hash', $qrHash)->first();

        if (!$billet) {
            exception: throw new Exception("Billet introuvable ou QR code invalide.");
        }

        if ($billet->trajet_id !== $trajetId) {
            throw new Exception("Ce billet n'appartient pas à ce trajet.");
        }

        if ($billet->statut === 'VALIDE') {
            throw new Exception("Ce billet a déjà été utilisé (déjà validé).");
        }

        if ($billet->statut === 'ANNULE') {
            throw new Exception("Ce billet a été annulé.");
        }

        // Mise à jour du statut du billet
        $billet->update([
            'statut' => 'VALIDE',
            'date_validation' => now(),
        ]);

        return $billet;
    }
}
