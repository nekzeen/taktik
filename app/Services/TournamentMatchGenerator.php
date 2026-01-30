<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\ArmyList;

class TournamentMatchGenerator
{
    public function generate(Tournament $tournament): array
    {
        // Récupérer les listes d'armée validées
        $validatedArmyLists = $tournament->armyLists()
            ->where('status', 'validated')
            ->with('user')
            ->get();

        // Vérifier qu'il y a au minimum 2 participants
        if ($validatedArmyLists->count() < 2) {
            return [
                'success' => false,
                'message' => 'Au minimum 2 participants validés sont nécessaires pour générer les matchs.',
            ];
        }

        // Supprimer les matchs existants
        TournamentMatch::where('tournament_id', $tournament->id)->delete();

        // Générer les matchs selon le format du tournoi
        $matches = match ($tournament->format) {
            'elimination' => $this->generateEliminationMatches($tournament, $validatedArmyLists),
            'swiss' => $this->generateSwissMatches($tournament, $validatedArmyLists),
            'league' => $this->generateLeagueMatches($tournament, $validatedArmyLists, []),
            default => [],
        };

        // Configurer automatiquement chaque match avec le pool de missions
        foreach ($matches as $match) {
            $match->randomizeSetup();
        }

        return [
            'success' => true,
            'message' => count($matches) . ' match(s) généré(s) et configuré(s) avec succès.',
            'matches_count' => count($matches),
        ];
    }

    /**
     * Génère les matchs en format élimination directe
     */
    private function generateEliminationMatches(Tournament $tournament, $armyLists): array
    {
        $matches = [];
        $players = $armyLists->shuffle()->values();
        $round = 1;
        $tableNumber = 1;

        // Premier round : appairage simple
        for ($i = 0; $i < $players->count(); $i += 2) {
            if (isset($players[$i + 1])) {
                $match = TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => $round,
                    'table_number' => $tableNumber,
                    'player1_id' => $players[$i]->user_id,
                    'player1_army_list_id' => $players[$i]->id,
                    'player2_id' => $players[$i + 1]->user_id,
                    'player2_army_list_id' => $players[$i + 1]->id,
                    'status' => 'pending',
                ]);
                $matches[] = $match;
                $tableNumber++;
            }
        }

        return $matches;
    }

    /**
     * Génère les matchs en format suisse (round-robin simplifié)
     */
    private function generateSwissMatches(Tournament $tournament, $armyLists): array
    {
        $matches = [];
        $players = $armyLists->shuffle()->values();
        $round = 1;
        $tableNumber = 1;

        // Générer 3 rounds de matchs suisse
        for ($r = 1; $r <= 3; $r++) {
            $players = $players->shuffle();
            
            for ($i = 0; $i < $players->count(); $i += 2) {
                if (isset($players[$i + 1])) {
                    $match = TournamentMatch::create([
                        'tournament_id' => $tournament->id,
                        'round' => $r,
                        'table_number' => ($i / 2) + 1,
                        'player1_id' => $players[$i]->user_id,
                        'player1_army_list_id' => $players[$i]->id,
                        'player2_id' => $players[$i + 1]->user_id,
                        'player2_army_list_id' => $players[$i + 1]->id,
                        'status' => 'pending',
                    ]);
                    $matches[] = $match;
                }
            }
        }

        return $matches;
    }

    /**
     * Génère les matchs en format ligue (round-robin complet)
     */
    private function generateLeagueMatches(Tournament $tournament, $armyLists, $completedMatches = []): array
    {
        $matches = [];
        $players = $armyLists->values();
        $round = 1;

        // Chaque joueur joue contre chaque autre joueur une fois
        for ($i = 0; $i < $players->count(); $i++) {
            for ($j = $i + 1; $j < $players->count(); $j++) {
                $p1_id = $players[$i]->user_id;
                $p2_id = $players[$j]->user_id;
                
                // Vérifier si ce match existe déjà (complété)
                $matchExists = in_array([
                    'p1' => min($p1_id, $p2_id),
                    'p2' => max($p1_id, $p2_id),
                ], $completedMatches);
                
                // Ne créer le match que s'il n'existe pas déjà
                if (!$matchExists) {
                    $match = TournamentMatch::create([
                        'tournament_id' => $tournament->id,
                        'round' => $round,
                        'table_number' => count($matches) + 1,
                        'player1_id' => $p1_id,
                        'player1_army_list_id' => $players[$i]->id,
                        'player2_id' => $p2_id,
                        'player2_army_list_id' => $players[$j]->id,
                        'status' => 'pending',
                    ]);
                    $matches[] = $match;
                }
            }
        }

        return $matches;
    }

    /**
     * Génère les matchs sans supprimer les matchs complétés
     */
    public function generateWithoutDeletingCompleted(Tournament $tournament): array
    {
        // Récupérer les listes d'armée validées
        $validatedArmyLists = $tournament->armyLists()
            ->where('status', 'validated')
            ->with('user')
            ->get();

        // Vérifier qu'il y a au minimum 2 participants
        if ($validatedArmyLists->count() < 2) {
            return [
                'success' => false,
                'message' => 'Au minimum 2 participants validés sont nécessaires.',
            ];
        }

        // Format league : génération incrémentale (ne modifie pas les matchs existants)
        if ($tournament->format === 'league') {
            $matches = $this->generateLeagueMatchesIncremental($tournament, $validatedArmyLists);

            // Configurer automatiquement UNIQUEMENT les nouveaux matchs
            foreach ($matches as $match) {
                $match->randomizeSetup();
            }

            return [
                'success' => true,
                'message' => count($matches) . ' match(s) ajouté(s) (format league, incrémental).',
                'matches_count' => count($matches),
            ];
        }

        // Autres formats : comportement historique (protège uniquement les matchs complétés)

        // Récupérer les matchs compléts pour éviter les doublons
        $completedMatches = TournamentMatch::where('tournament_id', $tournament->id)
            ->where('status', 'completed')
            ->get()
            ->map(fn($m) => [
                'p1' => min($m->player1_id, $m->player2_id),
                'p2' => max($m->player1_id, $m->player2_id),
            ])
            ->toArray();

        // Supprimer uniquement les matchs non complétés
        TournamentMatch::where('tournament_id', $tournament->id)
            ->where('status', '!=', 'completed')
            ->delete();

        // Générer les matchs selon le format du tournoi
        $matches = match ($tournament->format) {
            'elimination' => $this->generateEliminationMatches($tournament, $validatedArmyLists),
            'swiss' => $this->generateSwissMatches($tournament, $validatedArmyLists),
            'league' => $this->generateLeagueMatches($tournament, $validatedArmyLists, $completedMatches),
            default => [],
        };

        // Configurer automatiquement chaque match avec le pool de missions
        foreach ($matches as $match) {
            $match->randomizeSetup();
        }

        return [
            'success' => true,
            'message' => count($matches) . ' match(s) généré(s) et configuré(s).',
            'matches_count' => count($matches),
        ];
    }

    /**
     * Génère les matchs manquants en format ligue (round-robin) sans supprimer les matchs existants.
     */
    private function generateLeagueMatchesIncremental(Tournament $tournament, $armyLists): array
    {
        $matches = [];
        $players = $armyLists->values();

        // Index des matchs existants (tous statuts) par paire de joueurs (ordre indépendant)
        $existingPairs = TournamentMatch::where('tournament_id', $tournament->id)
            ->get()
            ->map(function ($match) {
                $players = collect([$match->player1_id, $match->player2_id])->sort()->values();
                return $players->join('-');
            })
            ->values();

        $maxTableNumber = TournamentMatch::where('tournament_id', $tournament->id)->max('table_number');
        $nextTableNumber = (int) ($maxTableNumber ?? 0);
        if ($nextTableNumber <= 0) {
            $nextTableNumber = (int) (TournamentMatch::where('tournament_id', $tournament->id)->max('id') ?? 0);
        }
        $nextTableNumber++;

        // Chaque joueur joue contre chaque autre joueur une fois
        for ($i = 0; $i < $players->count(); $i++) {
            for ($j = $i + 1; $j < $players->count(); $j++) {
                $p1Id = $players[$i]->user_id;
                $p2Id = $players[$j]->user_id;

                $pairKey = collect([$p1Id, $p2Id])->sort()->values()->join('-');
                if ($existingPairs->contains($pairKey)) {
                    continue;
                }

                $match = TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => 1,
                    'table_number' => $nextTableNumber,
                    'player1_id' => $p1Id,
                    'player1_army_list_id' => $players[$i]->id,
                    'player2_id' => $p2Id,
                    'player2_army_list_id' => $players[$j]->id,
                    'status' => 'pending',
                ]);

                $matches[] = $match;
                $nextTableNumber++;
            }
        }

        return $matches;
    }
}
