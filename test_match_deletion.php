<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\PlayerMatch;

echo "=== TEST SUPPRESSION MATCHS ===" . PHP_EOL;
echo "" . PHP_EOL;

// Récupérer un tournoi avec des matchs
$tournament = Tournament::with('tournamentMatches')->whereHas('tournamentMatches')->first();

if (!$tournament) {
    echo "✗ Aucun tournoi avec des matchs trouvé" . PHP_EOL;
    exit(1);
}

echo "Tournoi: {$tournament->name}" . PHP_EOL;
echo "" . PHP_EOL;

// Compter les matchs de tournoi
$tournamentMatchCount = $tournament->tournamentMatches->count();
echo "Matchs de tournoi: $tournamentMatchCount" . PHP_EOL;

// Compter les matchs libres (player_matches)
$playerMatchCount = PlayerMatch::count();
echo "Matchs libres (player_matches): $playerMatchCount" . PHP_EOL;
echo "" . PHP_EOL;

// Supprimer le tournoi
echo "Suppression du tournoi..." . PHP_EOL;
$tournamentId = $tournament->id;
$tournament->delete();

echo "✓ Tournoi supprimé" . PHP_EOL;
echo "" . PHP_EOL;

// Vérifier que les matchs de tournoi ont été supprimés
$remainingTournamentMatches = TournamentMatch::where('tournament_id', $tournamentId)->count();
if ($remainingTournamentMatches === 0) {
    echo "✓ PASS: Les matchs de tournoi ont bien été supprimés ($tournamentMatchCount supprimés)" . PHP_EOL;
} else {
    echo "✗ FAIL: $remainingTournamentMatches matchs de tournoi restent" . PHP_EOL;
}

// Vérifier que les matchs libres n'ont pas changé
$playerMatchCountAfter = PlayerMatch::count();
if ($playerMatchCountAfter === $playerMatchCount) {
    echo "✓ PASS: Les matchs libres n'ont pas été affectés ($playerMatchCount restent)" . PHP_EOL;
} else {
    echo "✗ FAIL: Le nombre de matchs libres a changé (avant: $playerMatchCount, après: $playerMatchCountAfter)" . PHP_EOL;
}

echo "" . PHP_EOL;
echo "✓ TEST TERMINÉ" . PHP_EOL;
?>
