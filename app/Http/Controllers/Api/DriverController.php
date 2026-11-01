<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PositionGps;
use App\Models\Trajet;
use App\Services\TicketService;
use Exception;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Obtenir le trajet actif du chauffeur connecté.
     */
    public function activeTrip(Request $request)
    {
        $trajet = Trajet::with(['bus', 'ligne'])
            ->where('chauffeur_id', $request->user()->id)
            ->whereIn('statut', ['PLANIFIE', 'EN_COURS'])
            ->orderBy('heure_depart_prevue', 'asc')
            ->first();

        if (! $trajet) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun trajet actif assigné pour le moment.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $trajet,
        ]);
    }

    /**
     * Scanner et valider un QR Code de billet.
     */
    public function validateTicket(Request $request, Trajet $trajet)
    {
        abort_unless($trajet->chauffeur_id === $request->user()->id, 403, 'Ce trajet ne vous est pas assigné.');

        $request->validate([
            'code_qr_hash' => 'required|string',
        ]);

        try {
            $billet = $this->ticketService->validateTicketByQrCode(
                $request->code_qr_hash,
                $trajet->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Billet valide. Bon voyage !',
                'data' => $billet,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Enregistrer la position GPS en temps réel transmise par l'application Flutter.
     */
    public function updateGpsPosition(Request $request, Trajet $trajet)
    {
        abort_unless($trajet->chauffeur_id === $request->user()->id, 403, 'Ce trajet ne vous est pas assigné.');

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $position = PositionGps::create([
            'trajet_id' => $trajet->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'recorded_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $position,
        ]);
    }
}
