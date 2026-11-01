<?php

namespace App\Services;

use App\Models\Billet;
use App\Models\Trajet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TicketService
{
    /**
     * Générer un nouveau billet pour un trajet donné.
     */
    public function createTicket(Trajet $trajet, array $data): Billet
    {
        return DB::transaction(function () use ($trajet, $data) {
            $trajet = Trajet::with('ligne')->lockForUpdate()->findOrFail($trajet->id);
            $bus = $trajet->bus()->lockForUpdate()->firstOrFail();

            if ($trajet->statut !== 'PLANIFIE') {
                throw new RuntimeException('Les billets ne peuvent être vendus que pour un trajet planifié.');
            }

            if ($bus->statut !== 'EN_SERVICE') {
                throw new RuntimeException('Le bus affecté à ce trajet n’est pas en service.');
            }

            $ticketsCount = $trajet->billets()->where('statut', '!=', 'ANNULE')->count();

            if ($ticketsCount >= $bus->capacite) {
                throw new RuntimeException('Désolé, ce bus est complet pour ce trajet.');
            }

            $seatNumber = $data['numero_siege'] ?? ($ticketsCount + 1);

            if ($seatNumber < 1 || $seatNumber > $bus->capacite) {
                throw new RuntimeException('Le numéro de siège doit être compris entre 1 et '.$bus->capacite.'.');
            }

            if ($trajet->billets()
                ->where('numero_siege', $seatNumber)
                ->where('statut', '!=', 'ANNULE')
                ->exists()) {
                throw new RuntimeException('Ce siège est déjà réservé pour ce trajet.');
            }

            $qrHash = 'MALITRANS-'.strtoupper(Str::random(32));

            return Billet::create([
                'code_qr_hash' => $qrHash,
                'trajet_id' => $trajet->id,
                'nom_passager' => $data['nom_passager'],
                'telephone_passager' => $data['telephone_passager'] ?? null,
                'numero_siege' => $seatNumber,
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
        return DB::transaction(function () use ($qrHash, $trajetId): Billet {
            $billet = Billet::where('code_qr_hash', $qrHash)->lockForUpdate()->first();

            if (! $billet) {
                throw new RuntimeException('Billet introuvable ou QR code invalide.');
            }

            if ($billet->trajet_id !== $trajetId) {
                throw new RuntimeException("Ce billet n'appartient pas à ce trajet.");
            }

            if ($billet->statut === 'VALIDE') {
                throw new RuntimeException('Ce billet a déjà été utilisé (déjà validé).');
            }

            if ($billet->statut === 'ANNULE') {
                throw new RuntimeException('Ce billet a été annulé.');
            }

            $billet->update([
                'statut' => 'VALIDE',
                'date_validation' => now(),
            ]);

            return $billet;
        });
    }
}
