<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trajet;
use App\Services\TicketService;
use Exception;
use Illuminate\Http\Request;

class GuichetController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Obtenir la liste des trajets disponibles pour une date donnée.
     */
    public function index(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
            'ligne_id' => 'nullable|exists:lignes,id',
        ]);

        $query = Trajet::with(['bus', 'ligne'])
            ->where('statut', 'PLANIFIE');

        if ($request->filled('date')) {
            $query->whereDate('heure_depart_prevue', $request->date);
        }

        if ($request->filled('ligne_id')) {
            $query->where('ligne_id', $request->ligne_id);
        }

        $trajets = $query->orderBy('heure_depart_prevue', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $trajets,
        ]);
    }

    /**
     * Vendre et générer un billet au guichet.
     */
    public function emettreBillet(Request $request, Trajet $trajet)
    {
        $validated = $request->validate([
            'nom_passager' => 'required|string|max:255',
            'telephone_passager' => 'nullable|string|max:20',
            'numero_siege' => 'nullable|integer',
            'prix' => 'nullable|numeric|min:0',
        ]);

        try {
            $validated['type_vente'] = 'GUICHET';
            $validated['vendu_par'] = $request->user()->id; // Guichetier connecté

            $billet = $this->ticketService->createTicket($trajet, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Billet émis avec succès.',
                'data' => $billet->load('trajet.ligne'),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
