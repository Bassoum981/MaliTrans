<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trajet;
use Illuminate\Http\Request;

class AdminTrajetController extends Controller
{
    /**
     * Créer un nouveau trajet (Réservé à l'Admin).
     */
    public function store(Request $request)
    {
        // Double sécurité : Vérifier que l'utilisateur connecté est bien admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Seul un administrateur peut planifier un trajet.',
            ], 403);
        }

        $validated = $request->validate([
            'ligne_id' => 'required|exists:lignes,id',
            'bus_id' => 'required|exists:buses,id',
            'chauffeur_id' => 'required|exists:users,id', // On peut s'assurer que c'est un chauffeur via une règle custom ou une vérification
            'heure_depart_prevue' => 'required|date|after:now',
            'prix_base' => 'required|numeric|min:0',
        ]);

        // Vérifier optionnellement que le user choisi a bien le rôle 'chauffeur'
        $chauffeur = \App\Models\User::find($validated['chauffeur_id']);
        if (!$chauffeur || $chauffeur->role !== 'chauffeur') {
            return response()->json([
                'success' => false,
                'message' => 'L utilisateur sélectionné doit obligatoirement avoir le rôle chauffeur.',
            ], 422);
        }

        // Création du trajet avec le statut initial PLANIFIE
        $trajet = Trajet::create([
            'ligne_id' => $validated['ligne_id'],
            'bus_id' => $validated['bus_id'],
            'chauffeur_id' => $validated['chauffeur_id'],
            'heure_depart_prevue' => $validated['heure_depart_prevue'],
            'prix_base' => $validated['prix_base'],
            'statut' => 'PLANIFIE',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trajet planifié avec succès.',
            'data' => $trajet->load(['ligne', 'bus', 'chauffeur']),
        ], 201);
    }
}
