<?php

namespace App\Console\Commands;

use App\Models\Tournament;
use App\Services\LeagueMatchGenerator;
use Illuminate\Console\Command;

class GenerateLeagueMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournament:generate-league-matches {tournament_id} {--force : Régénérer tous les matchs même s\'ils existent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère automatiquement tous les matchs pour un tournoi en format ligue (round-robin)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tournamentId = $this->argument('tournament_id');
        $force = $this->option('force');

        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            $this->error("❌ Tournoi #{$tournamentId} introuvable.");
            return 1;
        }

        if ($tournament->format !== 'league') {
            $this->error("❌ Ce tournoi n'est pas en format ligue (format actuel: {$tournament->format}).");
            return 1;
        }

        $this->info("📋 Tournoi : {$tournament->name}");
        $this->info("🎮 Format : Ligue (round-robin)");
        $this->newLine();

        $generator = new LeagueMatchGenerator();

        // Afficher les statistiques avant
        $statsBefore = $generator->getLeagueStats($tournament);
        $this->info("📊 Statistiques actuelles :");
        $this->line("   • Joueurs : {$statsBefore['players']}");
        $this->line("   • Matchs nécessaires : {$statsBefore['total_matches_needed']}");
        $this->line("   • Matchs créés : {$statsBefore['matches_created']}");
        $this->line("   • Matchs terminés : {$statsBefore['matches_completed']}");
        $this->line("   • Matchs manquants : {$statsBefore['matches_missing']}");
        $this->newLine();

        if ($statsBefore['matches_missing'] === 0 && !$force) {
            $this->info("✅ Tous les matchs sont déjà créés !");
            return 0;
        }

        if ($force) {
            $this->warn("⚠️  Mode force activé : les matchs existants seront ignorés.");
        }

        if (!$this->confirm('Voulez-vous générer les matchs ?', true)) {
            $this->info('Opération annulée.');
            return 0;
        }

        try {
            $result = $generator->generateLeagueMatches($tournament, !$force);

            $this->newLine();
            $this->info("✅ Génération terminée !");
            $this->line("   • Matchs créés : {$result['created']}");
            if ($result['skipped'] > 0) {
                $this->line("   • Matchs ignorés : {$result['skipped']} (déjà existants)");
            }
            $this->line("   • Total possible : {$result['total']}");
            $this->line("   • Joueurs : {$result['players']}");

            // Afficher les statistiques après
            $this->newLine();
            $statsAfter = $generator->getLeagueStats($tournament);
            $this->info("📊 Nouvelles statistiques :");
            $this->line("   • Progression : {$statsAfter['completion_percentage']}%");
            $this->line("   • Matchs créés : {$statsAfter['matches_created']} / {$statsAfter['total_matches_needed']}");

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erreur : {$e->getMessage()}");
            return 1;
        }
    }
}
