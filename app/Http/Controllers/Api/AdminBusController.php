<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use Illuminate\Http\Request;

class AdminBusController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'immatriculation' => 'required|string|unique:buses,immatriculation',
            'capacite' => 'required|integer|min:1',
            'statut' => 'sometimes|in:EN_SERVICE,EN_MAINTENANCE,HORS_SERVICE',
        ]);

        $bus = Bus::create([
            'immatriculation' => $validated['immatriculation'],
            'capacite' => $validated['capacite'],
            'statut' => $validated['statut'] ?? 'EN_SERVICE',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bus enregistré avec succès.',
            'data' => $bus,
        ], 201);
    }
}
