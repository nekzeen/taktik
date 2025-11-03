<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateSecondaryMissionsXml extends Command
{
    protected $signature = 'missions:validate-secondary-xml {file? : Path to XML file} {--source=chapter-approved-2025-26} {--strict : Mode strict - arrête à la première erreur}';
    protected $description = 'Valider les données des missions secondaires dans un fichier XML avant import';

    public function handle()
    {
        $file = $this->argument('file') ?? storage_path('missions/secondary-missions-chapter-approved-2025-26.xml');
        $strict = $this->option('strict');

        if (!file_exists($file)) {
            $this->error("❌ Fichier non trouvé: {$file}");
            return 1;
        }

        $this->info('🔍 Validation des missions secondaires XML...');
        $this->info('═══════════════════════════════════════════════════════════');

        try {
            $xml = simplexml_load_file($file);
            $missions = $xml->mission;

            if (empty($missions)) {
                $this->warn('⚠️  Aucune mission trouvée dans le fichier XML !');
                return 1;
            }

            $errors = [];
            $warnings = [];
            $missionCount = 0;

            foreach ($missions as $missionXml) {
                $missionCount++;
                $title = (string) $missionXml->title;
                
                $this->line("\n📋 Mission {$missionCount}: {$title}");
                
                // Vérification 1 : Titre présent
                if (empty($title)) {
                    $error = "Titre manquant";
                    $errors[] = $error;
                    $this->error("  {$error}");
                    if ($strict) return 1;
                    continue;
                }

                // Vérification 2 : Description présente
                $flavour = (string) $missionXml->flavour;
                if (empty($flavour)) {
                    $warning = "Description (flavour) manquante";
                    $warnings[] = $warning;
                    $this->warn("  {$warning}");
                }

                // Vérification 3 : Condition WHEN présente
                $when = (string) $missionXml->when;
                if (empty($when)) {
                    $error = "Condition WHEN manquante";
                    $errors[] = $error;
                    $this->error("  {$error}");
                    if ($strict) return 1;
                }

                // Vérification 4 : Items de scoring présents
                $scoringItems = [];
                if (isset($missionXml->scoring->item)) {
                    foreach ($missionXml->scoring->item as $item) {
                        $scoringItems[] = (string) $item;
                    }
                }

                if (empty($scoringItems)) {
                    $error = "Aucun item de scoring trouvé";
                    $errors[] = $error;
                    $this->error("  {$error}");
                    if ($strict) return 1;
                } else {
                    $this->line("  ✅ {$title} a " . count($scoringItems) . " item(s) de scoring");
                    
                    // Vérification 5 : Chaque item de scoring contient des points VP
                    foreach ($scoringItems as $index => $item) {
                        if (!preg_match('/\d+VP/i', $item)) {
                            $itemNum = $index + 1;
                            $warning = "Item de scoring {$itemNum} ne contient pas de points VP";
                            $warnings[] = $warning;
                            $this->warn("     {$warning}");
                        }
                    }
                }

                // Afficher les items de scoring
                if (!empty($scoringItems)) {
                    $this->line("  📊 Scoring items:");
                    foreach ($scoringItems as $index => $item) {
                        $this->line("     " . ($index + 1) . ". " . substr($item, 0, 80) . (strlen($item) > 80 ? '...' : ''));
                    }
                }
            }

            // Résumé
            $this->info("\n═══════════════════════════════════════════════════════════");
            $this->info("📊 Résultats de validation:");
            $this->line("  Total missions: {$missionCount}");
            $this->line("  Erreurs: " . count($errors));
            $this->line("  Avertissements: " . count($warnings));

            if (!empty($errors)) {
                $this->error("\nErreurs trouvées:");
                foreach ($errors as $error) {
                    $this->error("  - {$error}");
                }
                return 1;
            }

            if (!empty($warnings)) {
                $this->warn("\nAvertissements:");
                foreach ($warnings as $warning) {
                    $this->warn("  - {$warning}");
                }
                $this->info("\n✅ Validation réussie avec avertissements");
                return 0;
            }

            $this->info("\n✅ Validation réussie - Aucun problème détecté");
            return 0;

        } catch (\Exception $e) {
            $this->error("Erreur: {$e->getMessage()}");
            return 1;
        }
    }
}
