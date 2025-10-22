<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\ArmyList;
use Illuminate\Support\Collection;

class LeagueMatchGenerator
{
    /**
     * Génère tous les matchs pour un tournoi en format ligue (round-robin)
     * Chaque joueur affronte tous les autres joueurs une fois
     * 
     * @param Tournament $tournament
     * @param bool $skipExisting Si true, ne génère pas les matchs qui existent déjà
     * @return array ['created' => int, 'skipped' => int, 'total' => int]
     */
    public function generateLeagueMatches(Tournament $tournament, bool $skipExisting = true): array
    {
        // Récupérer toutes les listes d'armée validées
        $armyLists = $tournament->armyLists()
            ->where('status', 'validated')
            ->with('user')
            ->get();

        if ($armyLists->count() < 2) {
            throw new \Exception('Il faut au moins 2 joueurs avec des listes validées pour générer des matchs.');
        }

        $created = 0;
        $skipped = 0;
        $totalPossible = 0;

        // Récupérer les matchs existants si on doit les éviter
        $existingMatches = collect();
        if ($skipExisting) {
            $existingMatches = $tournament->tournamentMatches()
                ->get()
                ->map(function ($match) {
                    // Créer une clé unique pour chaque paire de joueurs (ordre indépendant)
                    $players = collect([$match->player1_id, $match->player2_id])->sort()->values();
                    return $players->join('-');
                });
        }

        // Générer tous les matchs possibles (round-robin)
        for ($i = 0; $i < $armyLists->count(); $i++) {
            for ($j = $i + 1; $j < $armyLists->count(); $j++) {
                $totalPossible++;
                
                $armyList1 = $armyLists[$i];
                $armyList2 = $armyLists[$j];

                // Créer une clé unique pour cette paire
                $pairKey = collect([$armyList1->user_id, $armyList2->user_id])->sort()->values()->join('-');

                // Vérifier si ce match existe déjà
                if ($skipExisting && $existingMatches->contains($pairKey)) {
                    $skipped++;
                    continue;
                }

                // Créer le match
                TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => 1, // Pour une ligue, on peut mettre tous les matchs au round 1
                    'table_number' => null,
                    'player1_id' => $armyList1->user_id,
                    'player1_army_list_id' => $armyList1->id,
                    'player2_id' => $armyList2->user_id,
                    'player2_army_list_id' => $armyList2->id,
                    'status' => 'pending',
                ]);

                $created++;
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'total' => $totalPossible,
            'players' => $armyLists->count(),
        ];
    }

    /**
     * Calcule le nombre total de matchs nécessaires pour un tournoi en ligue
     * Formule: n * (n-1) / 2 où n est le nombre de joueurs
     * 
     * @param int $playerCount
     * @return int
     */
    public function calculateTotalMatches(int $playerCount): int
    {
        if ($playerCount < 2) {
            return 0;
        }
        
        return ($playerCount * ($playerCount - 1)) / 2;
    }

    /**
     * Vérifie quels matchs manquent pour compléter la ligue
     * 
     * @param Tournament $tournament
     * @return Collection Collection de paires de joueurs manquantes
     */
    public function getMissingMatches(Tournament $tournament): Collection
    {
        $armyLists = $tournament->armyLists()
            ->where('status', 'validated')
            ->with('user')
            ->get();

        if ($armyLists->count() < 2) {
            return collect();
        }

        // Récupérer tous les matchs existants
        $existingMatches = $tournament->tournamentMatches()
            ->get()
            ->map(function ($match) {
                $players = collect([$match->player1_id, $match->player2_id])->sort()->values();
                return $players->join('-');
            });

        $missingMatches = collect();

        // Vérifier toutes les paires possibles
        for ($i = 0; $i < $armyLists->count(); $i++) {
            for ($j = $i + 1; $j < $armyLists->count(); $j++) {
                $armyList1 = $armyLists[$i];
                $armyList2 = $armyLists[$j];

                $pairKey = collect([$armyList1->user_id, $armyList2->user_id])->sort()->values()->join('-');

                if (!$existingMatches->contains($pairKey)) {
                    $missingMatches->push([
                        'player1' => $armyList1->user,
                        'player2' => $armyList2->user,
                        'player1_army_list' => $armyList1,
                        'player2_army_list' => $armyList2,
                    ]);
                }
            }
        }

        return $missingMatches;
    }

    /**
     * Obtient des statistiques sur l'avancement de la ligue
     * 
     * @param Tournament $tournament
     * @return array
     */
    public function getLeagueStats(Tournament $tournament): array
    {
        $armyLists = $tournament->armyLists()
            ->where('status', 'validated')
            ->get();

        $playerCount = $armyLists->count();
        $totalMatchesNeeded = $this->calculateTotalMatches($playerCount);
        
        $matches = $tournament->tournamentMatches;
        $completedMatches = $matches->where('status', 'completed')->count();
        $pendingMatches = $matches->where('status', 'pending')->count();
        $inProgressMatches = $matches->where('status', 'in_progress')->count();

        $missingMatches = $this->getMissingMatches($tournament);

        return [
            'players' => $playerCount,
            'total_matches_needed' => $totalMatchesNeeded,
            'matches_created' => $matches->count(),
            'matches_completed' => $completedMatches,
            'matches_pending' => $pendingMatches,
            'matches_in_progress' => $inProgressMatches,
            'matches_missing' => $missingMatches->count(),
            'completion_percentage' => $totalMatchesNeeded > 0 
                ? round(($completedMatches / $totalMatchesNeeded) * 100, 1) 
                : 0,
        ];
    }
}
