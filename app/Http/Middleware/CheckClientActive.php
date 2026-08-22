<?php

namespace App\Http\Middleware;

use App\Support\CurrentClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garantit qu'un client actif est bien défini pour la requête.
 *
 * Le client courant dépend du rôle (voir App\Support\CurrentClient) :
 *  - client      : le sien
 *  - super-admin : celui qu'il a sélectionné ; sans sélection, on l'y renvoie
 *  - employee    : celui de sa fiche enseignant
 */
class CheckClientActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $this->deny($request, 'Non authentifié.', 401);
        }

        $client = CurrentClient::get();

        // Un super-admin sans client sélectionné doit d'abord en choisir un.
        if (!$client && Auth::user()->hasRole('super-admin')) {
            return $this->deny(
                $request,
                'Sélectionnez d\'abord une école pour accéder à cet écran.',
                403,
                'admin.clients.index'
            );
        }

        if (!$client) {
            return $this->deny($request, 'Aucun client associé à votre compte.', 403);
        }

        if (!$client->is_active) {
            return $this->deny($request, 'Ce compte client est désactivé. Contactez l\'administration.', 403);
        }

        // Reste disponible pour le code qui lit $request->client.
        $request->merge(['client' => $client]);

        return $next($request);
    }

    private function deny(Request $request, string $message, int $status, ?string $route = null): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        if ($route) {
            return redirect()->route($route)->with('error', $message);
        }

        abort($status, $message);
    }
}
