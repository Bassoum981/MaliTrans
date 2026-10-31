<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ligne;
use Illuminate\Http\Request;

class AdminLigneController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => Ligne::all(),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validated =$request->validate([
            'ville_depart' => 'required|string',
            'ville_arrivee' => 'required|string',
            'prix_base' => 'required|numeric|min:0',
            'distance_km' => 'nullable|integer|min:0',
        ]);

        $ligne = Ligne::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ligne de transport créée avec succès.',
            'data' => $ligne,
        ], 201);
    }

    public function update(Request $request, Ligne$ligne)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validated =$request->validate([
            'ville_depart' => 'required|string',
            'ville_arrivee' => 'required|string',
            'prix_base' => 'required|numeric|min:0',
            'distance_km' => 'nullable|integer|min:0',
        ]);

        $ligne->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ligne mise à jour avec succès.',
            'data' => $ligne,
        ]);
    }

    public function destroy(Request $request, Ligne$ligne)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $ligne->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ligne supprimée avec succès.',
        ]);
    }
}
