<?php

use App\Http\Controllers\Api\AdminBusController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminLigneController;
use App\Http\Controllers\Api\AdminTrajetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\GuichetController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. ROUTES PUBLIQUES (Sans authentification)
// ==========================================
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// ==========================================
// 2. ROUTES PROTÉGÉES (Par Sanctum)
// ==========================================
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // --- SECTION AUTHENTIFICATION & PROFIL ---
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // --- SECTION ADMINISTRATION (Réservée aux admins) ---
    Route::prefix('admin')->middleware(['web', 'role:admin'])->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);

        // Employés
        Route::get('/users', [AdminController::class, 'indexEmployees']);
        Route::post('/users', [AdminController::class, 'storeEmployee']);
        Route::delete('/users/{user}', [AdminController::class, 'destroyEmployee']);

        // Bus
        Route::get('/buses', [AdminBusController::class, 'index']);
        Route::post('/buses', [AdminBusController::class, 'store']);
        Route::put('/buses/{bus}', [AdminBusController::class, 'update']);
        Route::delete('/buses/{bus}', [AdminBusController::class, 'destroy']);

        // Lignes
        Route::get('/lignes', [AdminLigneController::class, 'index']);
        Route::post('/lignes', [AdminLigneController::class, 'store']);
        Route::put('/lignes/{ligne}', [AdminLigneController::class, 'update']);
        Route::delete('/lignes/{ligne}', [AdminLigneController::class, 'destroy']);

        // Trajets
        Route::get('/trajets', [AdminTrajetController::class, 'index']);
        Route::post('/trajets', [AdminTrajetController::class, 'store']);
        Route::delete('/trajets/{trajet}', [AdminTrajetController::class, 'destroy']);
    });

    // --- SECTION GUICHET (Web / Guichetier) ---
    Route::prefix('guichet')->middleware(['web', 'role:guichetier'])->group(function () {
        Route::get('/trajets', [GuichetController::class, 'index']);
        Route::post('/trajets/{trajet}/billets', [GuichetController::class, 'emettreBillet']);
    });

    // --- SECTION CHAUFFEUR (Application Mobile Flutter) ---
    Route::prefix('driver')->middleware('role:chauffeur')->group(function () {
        Route::get('/trajet-actif', [DriverController::class, 'activeTrip']);
        Route::post('/trajets/{trajet}/valider-billet', [DriverController::class, 'validateTicket']);
        Route::post('/trajets/{trajet}/gps', [DriverController::class, 'updateGpsPosition']);
    });
});
