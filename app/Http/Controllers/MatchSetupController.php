<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\PlayerMatch;
use App\Models\StrikeForceDeploymentCard;
use App\Models\IncursionDeploymentCard;
use App\Models\AsymmetricWarfareDeploymentCard;
use App\Services\MatchSetupService;
use App\Services\ArmyPointsService;
use Illuminate\Http\Request;

class MatchSetupController extends Controller
{
    protected $setupService;

    public function __construct(MatchSetupService $setupService)
    {
        $this->setupService = $setupService;
    }

    /**
     * Afficher la page de configuration d'un match de tournoi
     */
    public function showTournamentMatch($tournament, $match)
    {
        // Récupérer le match
        $match = TournamentMatch::findOrFail($match);
        $tournament = \App\Models\Tournament::findOrFail($tournament);

        // Vérifier les permissions
        if ($match->tournament->created_by !== auth()->id() && !$match->canEditResult(auth()->user())) {
            abort(403, 'Non autorisé');
        }

        $options = $this->setupService->getAvailableOptions($match);

        return view('matches.setup', [
            'match' => $match,
            'tournament' => $tournament,
            'options' => $options,
            'armyPointsOptions' => ArmyPointsService::getArmyPointsOptions(),
            'matchType' => 'tournament',
        ]);
    }

    /**
     * Afficher la page de configuration d'un match simple
     */
    public function showPlayerMatch($playerMatch)
    {
        // Récupérer le match
        $match = PlayerMatch::findOrFail($playerMatch);

        // Vérifier les permissions - SEUL LE CRÉATEUR peut configurer
        if ($match->creator_id !== auth()->id()) {
            abort(403, 'Non autorisé - Seul le créateur du match peut configurer');
        }

        $options = $this->setupService->getAvailableOptions($match);

        return view('matches.setup', [
            'match' => $match,
            'options' => $options,
            'strikeForceDeploymentCards' => StrikeForceDeploymentCard::active()->orderBy('name')->get(),
            'incursionDeploymentCards' => IncursionDeploymentCard::active()->orderBy('name')->get(),
            'asymmetricWarfareDeploymentCards' => AsymmetricWarfareDeploymentCard::active()->orderBy('name')->get(),
            'armyPointsOptions' => ArmyPointsService::getArmyPointsOptions(),
            'matchType' => 'player',
        ]);
    }

    /**
     * Sauvegarder la configuration aléatoire
     */
    public function randomizeTournamentMatch(Request $request, $tournament, $match)
    {
        $match = TournamentMatch::findOrFail($match);
        
        if ($match->tournament->created_by !== auth()->id() && !$match->canEditResult(auth()->user())) {
            abort(403, 'Non autorisé');
        }

        $mode = $request->input('randomize_mode', 'normal');
        $this->setupService->randomizeMatch($match, $mode);

        return redirect()->back()->with('success', 'Configuration du match générée aléatoirement avec succès');
    }

    /**
     * Sauvegarder la configuration aléatoire pour un match simple
     */
    public function randomizePlayerMatch(Request $request, $playerMatch)
    {
        $match = PlayerMatch::findOrFail($playerMatch);
        
        // SEUL LE CRÉATEUR peut configurer
        if ($match->creator_id !== auth()->id()) {
            abort(403, 'Non autorisé - Seul le créateur du match peut configurer');
        }

        $mode = $request->input('randomize_mode', 'normal');
        $this->setupService->randomizeMatch($match, $mode);

        return redirect()->back()->with('success', 'Configuration du match générée aléatoirement avec succès');
    }

    /**
     * Sauvegarder la configuration manuelle
     */
    public function updateTournamentMatch(Request $request, $tournament, $match)
    {
        $match = TournamentMatch::findOrFail($match);
        
        if ($match->tournament->created_by !== auth()->id() && !$match->canEditResult(auth()->user())) {
            abort(403, 'Non autorisé');
        }

        $validated = $request->validate([
            'primary_mission_id' => 'nullable|exists:primary_missions,id',
            'terrain_layout_id' => 'required|exists:terrain_layouts,id',
            'twist_mission_id' => 'nullable|exists:twist_missions,id',
            'asymmetric_primary_mission_id' => 'nullable|exists:asymmetric_primary_missions,id',
            'deployment_mode' => 'nullable|string',
            'army_points' => 'nullable|in:1000,1500,2000,3000,3000+',
        ]);

        // Si une mission asymétrique est définie, ne pas utiliser la mission primaire normale
        $asymmetricMissionId = $validated['asymmetric_primary_mission_id'] ?? null;
        if ($asymmetricMissionId) {
            $primaryMissionId = null;
        } else {
            $primaryMissionId = $validated['primary_mission_id'];
        }

        $this->setupService->updateMatchSetup(
            $match,
            $primaryMissionId,
            $validated['terrain_layout_id'],
            $validated['twist_mission_id'] ?? null,
            $asymmetricMissionId
        );

        // Sauvegarder les points d'armée
        if ($validated['army_points']) {
            $match->army_points = $validated['army_points'];
            
            // Définir automatiquement le mode de déploiement basé sur les points d'armée
            $deploymentMode = ArmyPointsService::getDeploymentModeByArmyPoints($validated['army_points']);
            if ($deploymentMode) {
                $match->deployment_mode = $deploymentMode;
            }
        }

        // Sauvegarder la zone de déploiement si fournie (override automatique)
        if ($validated['deployment_mode']) {
            $match->deployment_mode = $validated['deployment_mode'];
        }

        $match->save();

        return redirect()->route('tournaments.matches.summary', [$match->tournament_id, $match->id])->with('success', 'Configuration du match sauvegardée');
    }

    /**
     * Sauvegarder la configuration manuelle pour un match simple
     */
    public function updatePlayerMatch(Request $request, $playerMatch)
    {
        $match = PlayerMatch::findOrFail($playerMatch);
        
        // SEUL LE CRÉATEUR peut configurer
        if ($match->creator_id !== auth()->id()) {
            abort(403, 'Non autorisé - Seul le créateur du match peut configurer');
        }

        $validated = $request->validate([
            'primary_mission_id' => 'nullable|exists:primary_missions,id',
            'terrain_layout_id' => 'required|exists:terrain_layouts,id',
            'twist_mission_id' => 'nullable|exists:twist_missions,id',
            'asymmetric_primary_mission_id' => 'nullable|exists:asymmetric_primary_missions,id',
            'deployment_mode' => 'nullable|string',
            'army_points' => 'nullable|in:1000,1500,2000,3000,3000+',
        ]);

        // Si une mission asymétrique est définie, ne pas utiliser la mission primaire normale
        $asymmetricMissionId = $validated['asymmetric_primary_mission_id'] ?? null;
        if ($asymmetricMissionId) {
            $primaryMissionId = null;
        } else {
            $primaryMissionId = $validated['primary_mission_id'];
        }

        $this->setupService->updateMatchSetup(
            $match,
            $primaryMissionId,
            $validated['terrain_layout_id'],
            $validated['twist_mission_id'] ?? null,
            $asymmetricMissionId
        );

        // Sauvegarder les points d'armée
        if ($validated['army_points']) {
            $match->army_points = $validated['army_points'];
            
            // Définir automatiquement le mode de déploiement basé sur les points d'armée
            $deploymentMode = ArmyPointsService::getDeploymentModeByArmyPoints($validated['army_points']);
            if ($deploymentMode) {
                $match->deployment_mode = $deploymentMode;
            }
        }

        // Sauvegarder la zone de déploiement si fournie (override automatique)
        if ($validated['deployment_mode']) {
            $match->deployment_mode = $validated['deployment_mode'];
        }

        $match->save();

        return redirect()->route('player-matches.summary', $match->id)->with('success', 'Configuration du match sauvegardée');
    }

    /**
     * Réinitialiser la configuration
     */
    public function resetTournamentMatch($tournament, $match)
    {
        $match = TournamentMatch::findOrFail($match);
        
        if ($match->tournament->created_by !== auth()->id() && !$match->canEditResult(auth()->user())) {
            abort(403, 'Non autorisé');
        }

        $this->setupService->resetSetup($match);

        return redirect()->back()->with('success', 'Configuration du match réinitialisée');
    }

    /**
     * Réinitialiser la configuration pour un match simple
     */
    public function resetPlayerMatch($playerMatch)
    {
        $match = PlayerMatch::findOrFail($playerMatch);
        
        // SEUL LE CRÉATEUR peut réinitialiser
        if ($match->creator_id !== auth()->id()) {
            abort(403, 'Non autorisé - Seul le créateur du match peut réinitialiser');
        }

        $this->setupService->resetSetup($match);

        return redirect()->back()->with('success', 'Configuration du match réinitialisée');
    }

    /**
     * Afficher le résumé de la configuration du match de tournoi
     */
    public function showTournamentSummary($tournament, $match)
    {
        $tournament = Tournament::findOrFail($tournament);
        $match = TournamentMatch::findOrFail($match);

        if ($match->tournament_id !== $tournament->id) {
            abort(404);
        }

        $user = auth()->user();
        
        // Vérifier les permissions : joueurs du match, créateur du tournoi, ou super-admin
        $isPlayer = $match->isPlayer($user);
        $isCreator = $tournament->created_by === $user->id;
        $isSuperAdmin = $user->hasRole('super-admin');
        
        if (!$isPlayer && !$isCreator && !$isSuperAdmin) {
            abort(403, 'Non autorisé');
        }

        return view('matches.summary', [
            'tournament' => $tournament,
            'match' => $match,
            'matchType' => 'tournament',
        ]);
    }

    /**
     * Afficher le résumé de la configuration du match simple
     */
    public function showPlayerSummary($playerMatch)
    {
        $match = PlayerMatch::findOrFail($playerMatch);

        // Permissions :
        // - Créateur : peut toujours voir le résumé (pour pouvoir valider)
        // - Adversaire : peut voir seulement si la configuration est validée
        if ($match->creator_id !== auth()->id() && !$match->is_setup_validated) {
            abort(403, 'Non autorisé - La configuration du match n\'est pas encore validée');
        }

        return view('matches.summary', [
            'match' => $match,
            'matchType' => 'player',
        ]);
    }

    /**
     * Valider la configuration du match simple
     */
    public function validatePlayerSetup($playerMatch)
    {
        $match = PlayerMatch::findOrFail($playerMatch);

        // SEUL LE CRÉATEUR peut valider
        if ($match->creator_id !== auth()->id()) {
            abort(403, 'Non autorisé - Seul le créateur du match peut valider');
        }

        // Marquer la configuration comme validée
        $match->is_setup_validated = true;
        $match->save();

        return redirect()->route('player-matches.index')->with('success', 'Configuration du match validée avec succès');
    }
}
