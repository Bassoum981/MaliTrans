<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function indexEmployees(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => User::whereIn('role', ['admin', 'guichetier', 'chauffeur'])->get(),
        ]);
    }

    public function storeEmployee(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|in:guichetier,chauffeur',
        ]);

        $employee = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Compte employé créé avec succès.',
            'data' => $employee,
        ], 201);
    }

    public function destroyEmployee(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Impossible de supprimer votre propre compte.'], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employé supprimé avec succès.',
        ]);
    }
}
