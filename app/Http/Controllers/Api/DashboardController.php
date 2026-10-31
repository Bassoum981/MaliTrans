<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Ligne;
use App\Models\Trajet;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Récupérer les statistiques globales pour le tableau de bord admin.
     */
    public function stats(Request $request)
    {
        // Sécurité : Vérifier que l'utilisateur est administrateur
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Réservé aux administrateurs.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'buses_count' => Bus::count(),
                'lignes_count' => Ligne::count(),
                'drivers_count' => User::where('role', 'chauffeur')->count(),
                'trajets_count' => Trajet::count(),
            ],
        ]);
    }
}
