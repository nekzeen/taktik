<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Webhook pour déclencher la mise à jour automatique des missions
     * 
     * Usage:
     * POST /api/webhooks/missions-update
     * Headers: X-Webhook-Token: your-secret-token
     */
    public function updateMissions(Request $request)
    {
        // Vérifier le token de sécurité
        $token = config('app.webhook_token');
        if (!$token || $request->header('X-Webhook-Token') !== $token) {
            Log::warning('Webhook missions-update: Token invalide');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            Log::info('Webhook missions-update: Démarrage de la mise à jour automatique');

            // Exécuter le workflow complet
            Artisan::call('missions:update-and-validate', [
                '--types' => $request->input('types', 'primary,secondary,twist,asymmetric,strike-force,incursion,asymmetric-warfare'),
            ]);

            $output = Artisan::output();
            Log::info('Webhook missions-update: Mise à jour terminée', ['output' => $output]);

            return response()->json([
                'success' => true,
                'message' => 'Mise à jour des missions démarrée',
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            Log::error('Webhook missions-update: Erreur', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Erreur lors de la mise à jour',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook pour déclencher la validation uniquement
     */
    public function validateMissions(Request $request)
    {
        $token = config('app.webhook_token');
        if (!$token || $request->header('X-Webhook-Token') !== $token) {
            Log::warning('Webhook missions-validate: Token invalide');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            Log::info('Webhook missions-validate: Démarrage de la validation');

            $type = $request->input('type', 'primary');
            
            Artisan::call('missions:compare-wahapedia', [
                '--type' => $type,
            ]);

            $output = Artisan::output();
            Log::info('Webhook missions-validate: Validation terminée', ['output' => $output]);

            return response()->json([
                'success' => true,
                'message' => 'Validation des missions démarrée',
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            Log::error('Webhook missions-validate: Erreur', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Erreur lors de la validation',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
