<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ligne;
use Illuminate\Http\Request;

class AdminLigneController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
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
}
