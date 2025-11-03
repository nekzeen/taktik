<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateAndValidateMissions extends Command
{
    protected $signature = 'missions:update-and-validate {--types=primary,secondary,twist,asymmetric,strike-force,incursion,asymmetric-warfare : Types de missions à mettre à jour} {--dry-run : Afficher les changements sans les appliquer}';
    protected $description = 'Mettre à jour et valider TOUTES les missions (workflow complet)';

    public function handle()
    {
        $typesString = $this->option('types');
        $types = array_map('trim', explode(',', $typesString));
        $dryRun = $this->option('dry-run');

        $this->info('🚀 Workflow Complet de Mise à Jour des Missions');
        $this->info('═══════════════════════════════════════════════════════════');

        if ($dryRun) {
            $this->warn('⚠️  Mode DRY-RUN : Les changements ne seront PAS appliqués');
            $this->line('');
        }

        $this->info("📊 Types de missions à traiter: " . implode(', ', $types));
        $this->line('');

        foreach ($types as $type) {
            $this->processType($type, $dryRun);
        }

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Workflow Complet Terminé');
        
        if ($dryRun) {
            $this->warn('⚠️  Mode DRY-RUN : Aucun changement n\'a été appliqué');
            $this->info('Exécutez sans --dry-run pour appliquer les changements');
        }

        return 0;
    }

    protected function processType(string $type, bool $dryRun): void
    {
        $this->line('');
        $this->info("🔄 Traitement: {$type}");
        $this->line('─────────────────────────────────────────────────────────────');

        // Étape 1 : Valider le XML
        $this->line('Étape 1/4 : Validation du XML...');
        $this->call('missions:validate-xml', [
            '--strict' => true,
        ]);

        // Étape 2 : Importer les missions
        $this->line('');
        $this->line('Étape 2/4 : Import des missions...');
        $this->call('missions:import-xml');

        // Étape 3 : Comparer avec Wahapedia
        $this->line('');
        $this->line('Étape 3/4 : Comparaison avec Wahapedia...');
        $this->call('missions:compare-wahapedia', [
            '--type' => $type,
        ]);

        // Étape 4 : Corriger les discrepancies
        $this->line('');
        $this->line('Étape 4/4 : Correction des discrepancies...');
        
        $options = ['--type' => $type];
        if ($dryRun) {
            $options['--dry-run'] = true;
        }
        
        $this->call('missions:fix-discrepancies', $options);

        // Vérification finale
        $this->line('');
        $this->line('Vérification finale...');
        $this->call('missions:compare-wahapedia', [
            '--type' => $type,
        ]);

        $this->info("✅ {$type} : Workflow Terminé");
    }
}
