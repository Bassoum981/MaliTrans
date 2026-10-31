<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\GuichetController;
use App\Http\Controllers\Api\DriverController;

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
    Route::prefix('admin')->group(function () {
        Route::post('/users', [AdminController::class, 'storeEmployee']);
    });

    // --- SECTION GUICHET (Web / Guichetier) ---
    Route::prefix('guichet')->group(function () {
        Route::get('/trajets', [GuichetController::class, 'index']);
        Route::post('/trajets/{trajet}/billets', [GuichetController::class, 'emettreBillet']);
    });

    // --- SECTION CHAUFFEUR (Application Mobile Flutter) ---
    Route::prefix('driver')->group(function () {
        Route::get('/trajet-actif', [DriverController::class, 'activeTrip']);
        Route::post('/trajets/{trajet}/valider-billet', [DriverController::class, 'validateTicket']);
        Route::post('/trajets/{trajet}/gps', [DriverController::class, 'updateGpsPosition']);
    });
});
