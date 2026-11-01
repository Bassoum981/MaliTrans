<?php

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirection de la racine vers la page de connexion
Route::get('/', function () {
    return redirect()->route('login');
});

// Route publique de connexion
Route::view('/login', 'auth.login')->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Identifiants incorrects ou compte inexistant.',
        ], 422);
    }

    if (! in_array($request->user()->role, ['admin', 'guichetier'], true)) {
        Auth::logout();

        return response()->json([
            'message' => 'Ce compte ne possède pas accès à une interface web.',
        ], 403);
    }

    $request->session()->regenerate();

    $redirect = $request->user()->role === 'admin'
        ? route('admin.dashboard')
        : route('guichet.dashboard');

    return response()->json([
        'success' => true,
        'redirect' => $redirect,
    ]);
})->name('login.authenticate');

Route::get('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

// Routes d'administration (protégées par le front avec le token Sanctum)
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::view('/buses', 'admin.buses')->name('admin.buses');
    Route::view('/lignes', 'admin.lignes')->name('admin.lignes');
    Route::view('/trajets', 'admin.trajets')->name('admin.trajets');
    Route::view('/users', 'admin.users')->name('admin.users');
});

Route::prefix('guichet')->middleware(['auth', 'role:guichetier'])->group(function () {
    Route::view('/', 'guichet.dashboard')->name('guichet.dashboard');
});
