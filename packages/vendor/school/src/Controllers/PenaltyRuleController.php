<?php

namespace Vendor\School\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\PenaltyRule;
use Illuminate\Http\Request;

class PenaltyRuleController extends Controller
{
    public function index(Request $request)
    {
        $client = Client::where('user_id', auth()->id())->first();

        $penaltyRule = PenaltyRule::forClientOrDefaults($client->id ?? 0);

        return view('school::penalty-rules.index', compact('penaltyRule'));
    }

    public function update(Request $request)
    {
        try {
            $client = Client::where('user_id', auth()->id())->first();

            if (!$client) {
                return response()->json(['success' => false, 'message' => 'Client non trouvé.'], 404);
            }

            $validated = $request->validate([
                'absence_count' => 'required|integer|min:1',
                'absence_rate' => 'required|numeric|min:0|max:100',
                'late_minutes' => 'required|integer|min:1',
                'late_rate' => 'required|numeric|min:0|max:100',
            ]);

            $penaltyRule = PenaltyRule::updateOrCreate(
                ['client_id' => $client->id],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Règles de pénalités mises à jour avec succès.',
                'data' => $penaltyRule,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage(),
            ], 500);
        }
    }
}
