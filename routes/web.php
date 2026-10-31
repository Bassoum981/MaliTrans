<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;

// Redirection de la racine vers la page de connexion
Route::get('/', function () {
    return redirect()->route('login');
});

// Route publique de connexion
Route::view('/login', 'auth.login')->name('login');

// Routes d'administration (protégées par le front avec le token Sanctum)
Route::prefix('admin')->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::view('/buses', 'admin.buses')->name('admin.buses');
    Route::view('/lignes', 'admin.lignes')->name('admin.lignes');
    Route::view('/trajets', 'admin.trajets')->name('admin.trajets');
    Route::view('/users', 'admin.users')->name('admin.users');
});
