<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trajet;
use App\Models\User;
use Illuminate\Http\Request;

class AdminTrajetController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $trajets = Trajet::with(['ligne', 'bus', 'chauffeur'])
            ->orderBy('heure_depart_prevue', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $trajets,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'ligne_id' => 'required|exists:lignes,id',
            'bus_id' => 'required|exists:buses,id',
            'chauffeur_id' => 'required|exists:users,id',
            'heure_depart_prevue' => 'required|date|after:now',
        ]);

        $chauffeur = User::find($validated['chauffeur_id']);
        if (! $chauffeur || $chauffeur->role !== 'chauffeur') {
            return response()->json([
                'success' => false,
                'message' => 'L utilisateur sélectionné doit obligatoirement avoir le rôle chauffeur.',
            ], 422);
        }

        $trajet = Trajet::create([
            'ligne_id' => $validated['ligne_id'],
            'bus_id' => $validated['bus_id'],
            'chauffeur_id' => $validated['chauffeur_id'],
            'heure_depart_prevue' => $validated['heure_depart_prevue'],
            'statut' => 'PLANIFIE',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trajet planifié avec succès.',
            'data' => $trajet->load(['ligne', 'bus', 'chauffeur']),
        ], 201);
    }

    public function destroy(Request $request, Trajet $trajet)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $trajet->delete();

        return response()->json([
            'success' => true,
            'message' => 'Trajet supprimé avec succès.',
        ]);
    }
}
