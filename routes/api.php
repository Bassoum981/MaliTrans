<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GuichetController;
use App\Http\Controllers\Api\DriverController;

// 1. Authentification Sanctum (Login)
// Route::post('/v1/auth/login', [AuthController::class, 'login']);

// Routes protégées par authentification Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Infos de l'utilisateur connecté & Déconnexion
    Route::get('/v1/auth/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });
    // Route::post('/v1/auth/logout', [AuthController::class, 'logout']);

    // --- SECTION GUICHET (Web / Guichetier) ---
    Route::prefix('v1/guichet')->group(function () {
        Route::get('/trajets', [GuichetController::class, 'index']);
        Route::post('/trajets/{trajet}/billets', [GuichetController::class, 'emettreBillet']);
    });

    // --- SECTION CHAUFFEUR (Application Mobile Flutter) ---
    Route::prefix('v1/driver')->group(function () {
        Route::get('/trajet-actif', [DriverController::class, 'activeTrip']);
        Route::post('/trajets/{trajet}/valider-billet', [DriverController::class, 'validateTicket']);
        Route::post('/trajets/{trajet}/gps', [DriverController::class, 'updateGpsPosition']);
    });
});
