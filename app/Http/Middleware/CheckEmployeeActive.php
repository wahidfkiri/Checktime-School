<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class CheckEmployeeActive
{
    /**
     * Vérifie que l'utilisateur connecté est bien un enseignant actif, rattaché
     * à un établissement (client) lui-même actif. Équivalent de CheckClientActive
     * pour le rôle 'employee'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $user = Auth::user();

        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Profil enseignant introuvable.'
            ], 403);
        }

        if ($employee->status !== 'active') {
            return response()->json([
                'message' => 'Votre compte est inactif. Contactez l\'administration.'
            ], 403);
        }

        if (!$employee->client || !$employee->client->is_active) {
            return response()->json([
                'message' => 'L\'établissement associé est inactif.'
            ], 403);
        }

        $request->merge(['employee' => $employee]);

        return $next($request);
    }
}
