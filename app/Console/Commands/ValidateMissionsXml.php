<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateMissionsXml extends Command
{
    protected $signature = 'missions:validate-xml {file? : Path to XML file} {--source=chapter-approved-2025-26} {--strict : Mode strict - arrête à la première erreur}';
    protected $description = 'Valider les données des missions dans un fichier XML avant import';

    public function handle()
    {
        $file = $this->argument('file') ?? storage_path('missions/primary-missions-chapter-approved-2025-26.xml');
        $strict = $this->option('strict');

        if (!file_exists($file)) {
            $this->error("❌ Fichier non trouvé: {$file}");
            return 1;
        }

        $this->info('🔍 Validation des missions XML...');
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
                    $error = "❌ Titre manquant";
                    $errors[] = $error;
                    $this->error("  {$error}");
                    if ($strict) return 1;
                    continue;
                }

                // Vérification 2 : Description (flavour) présente
                $flavour = (string) $missionXml->flavour;
                if (empty($flavour)) {
                    $warning = "⚠️  Description (flavour) manquante";
                    $warnings[] = $warning;
                    $this->warn("  {$warning}");
                }

                // Vérification 3 : Condition WHEN présente
                $when = (string) $missionXml->when;
                if (empty($when)) {
                    $error = "❌ Condition WHEN manquante";
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
                    $error = "❌ Aucun item de scoring trouvé";
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

                // Vérification 6 : Vérifier la structure du WHEN
                if (!empty($when)) {
                    $this->validateWhenStructure($when, $title, $errors, $warnings, $strict);
                }

                // Vérification 7 : Vérifier les doublons potentiels
                $this->validateForDuplicates($title, $errors, $warnings);

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
                $this->error("\n❌ Erreurs trouvées:");
                foreach ($errors as $error) {
                    $this->error("  - {$error}");
                }
                return 1;
            }

            if (!empty($warnings)) {
                $this->warn("\n⚠️  Avertissements:");
                foreach ($warnings as $warning) {
                    $this->warn("  - {$warning}");
                }
                $this->info("\n✅ Validation réussie avec avertissements");
                return 0;
            }

            $this->info("\n✅ Validation réussie - Aucun problème détecté");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            return 1;
        }
    }

    protected function validateWhenStructure(string $when, string $title, &$errors, &$warnings, bool $strict): void
    {
        // Vérifier que WHEN contient un tiret ou "WHEN:"
        if (!preg_match('/\s*-\s*|\s+WHEN:/i', $when)) {
            $warning = "Structure WHEN potentiellement incorrecte: {$when}";
            $warnings[] = $warning;
            $this->warn("  {$warning}");
        }

        // Vérifier les timing courants
        $validTimings = ['ANY BATTLE ROUND', 'SECOND BATTLE ROUND', 'FIRST BATTLE ROUND', 'THIRD BATTLE ROUND'];
        $hasValidTiming = false;
        
        foreach ($validTimings as $timing) {
            if (stripos($when, $timing) !== false) {
                $hasValidTiming = true;
                break;
            }
        }

        if (!$hasValidTiming) {
            $warning = "Timing non reconnu dans WHEN: {$when}";
            $warnings[] = $warning;
            $this->warn("  {$warning}");
        }
    }

    protected function validateForDuplicates(string $title, &$errors, &$warnings): void
    {
        // Vérifier les doublons dans la base de données
        $existing = \DB::table('primary_missions')
            ->where('name', $title)
            ->count();

        if ($existing > 0) {
            $warning = "Mission '{$title}' existe déjà en base de données (sera mise à jour)";
            $warnings[] = $warning;
            $this->line("  {$warning}");
        }
    }
}
