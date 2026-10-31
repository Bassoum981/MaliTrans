<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use Illuminate\Http\Request;

class AdminBusController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => Bus::all(),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

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

    public function update(Request $request, Bus $bus)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'immatriculation' => 'required|string|unique:buses,immatriculation,' . $bus->id,
            'capacite' => 'required|integer|min:1',
            'statut' => 'required|in:EN_SERVICE,EN_MAINTENANCE,HORS_SERVICE',
        ]);

        $bus->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bus mis à jour avec succès.',
            'data' => $bus,
        ]);
    }

    public function destroy(Request $request, Bus $bus)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $bus->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bus supprimé avec succès.',
        ]);
    }
}
