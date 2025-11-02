<?php

namespace App\Console\Commands;

use App\Models\TwistMission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportTwistMissionsInteractive extends Command
{
    protected $signature = 'twist:import-interactive';
    protected $description = 'Importer les péripéties en mode interactif (copier-coller les textes)';

    public function handle()
    {
        $this->info('🎯 IMPORTATION INTERACTIVE DES PÉRIPÉTIES');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('');
        $this->line('📝 Instructions:');
        $this->line('   1. Copiez le texte complet d\'une péripétie depuis Wahapedia');
        $this->line('   2. Collez-le ci-dessous');
        $this->line('   3. Le système extraira le nom et le texte automatiquement');
        $this->line('');

        $twistMissions = [];
        $continue = true;

        while ($continue) {
            $this->info('');
            $this->line('📋 Entrez le texte complet de la péripétie (ou "done" pour terminer):');
            $this->line('');

            $fullText = $this->ask('Texte complet');

            if (strtolower($fullText) === 'done') {
                $continue = false;
                break;
            }

            // Extraire le nom (première ligne ou première phrase en majuscules)
            $lines = explode("\n", trim($fullText));
            $firstLine = trim($lines[0]);
            
            // Si la première ligne est vide, chercher la première ligne non-vide
            if (empty($firstLine)) {
                $this->error('❌ Le texte ne peut pas être vide');
                continue;
            }
            
            // Extraire le nom: prendre jusqu'au premier point ou première ligne
            $name = $firstLine;
            
            // Si le nom contient un point, prendre jusqu'au point
            if (strpos($name, '.') !== false) {
                $name = trim(explode('.', $name)[0]);
            }
            
            // Si le nom est trop long, c'est probablement qu'il n'y a pas de séparation
            // Chercher le premier mot en majuscules suivi de minuscules
            if (strlen($name) > 100) {
                // Chercher un motif: MAJUSCULES suivi de minuscules
                if (preg_match('/^([A-Z\s]+)([A-Z][a-z])/u', $name, $matches)) {
                    $name = trim($matches[1]);
                } else {
                    // Prendre les premiers mots en majuscules
                    $words = explode(' ', $name);
                    $name = '';
                    foreach ($words as $word) {
                        if (strtoupper($word) === $word && strlen($word) > 1) {
                            $name .= ($name ? ' ' : '') . $word;
                        } else {
                            break;
                        }
                    }
                }
            }
            
            $name = trim($name);

            // Vérifier que le nom est valide
            if (strlen($name) < 3 || strlen($name) > 100) {
                $this->error('❌ Le nom doit contenir entre 3 et 100 caractères (trouvé: ' . strlen($name) . ')');
                continue;
            }

            // Extraire la description (deuxième ligne)
            $description = isset($lines[1]) ? trim($lines[1]) : '';

            // Extraire l'effet (à partir de la 3e ligne)
            $effectLines = array_slice($lines, 2);
            $effect = implode("\n", $effectLines);

            $this->info('');
            $this->line('✅ Péripétie détectée:');
            $this->line('   Nom: ' . $name);
            $this->line('   Description: ' . substr($description, 0, 60) . '...');
            $this->line('   Effet: ' . substr($effect, 0, 60) . '...');
            $this->info('');

            $confirm = $this->confirm('Confirmer cette péripétie?', true);

            if ($confirm) {
                $twistMissions[] = [
                    'name' => $name,
                    'description' => $description,
                    'full_text' => $fullText,
                    'effect' => $effect,
                    'edition' => 'Chapter Approved 2025-26',
                    'source' => 'chapter-approved-2025-26',
                ];

                $this->line('✅ Péripétie ajoutée (' . count($twistMissions) . ')');
            } else {
                $this->line('⏭️  Péripétie ignorée');
            }
        }

        if (empty($twistMissions)) {
            $this->warn('⚠️  Aucune péripétie à importer');
            return 1;
        }

        // Générer le fichier JSON
        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('💾 Génération du fichier JSON...');

        $jsonPath = storage_path('missions/twist-missions-complete-texts.json');
        $jsonData = [
            'twist_missions' => $twistMissions,
        ];

        File::put($jsonPath, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->line("✅ Fichier généré: {$jsonPath}");

        // Importer les données
        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📥 Importation des données...');

        $created = 0;
        $skipped = 0;

        foreach ($twistMissions as $data) {
            // Vérifier si elle existe déjà
            $existing = TwistMission::where('name', $data['name'])
                ->where('source', $data['source'])
                ->first();

            if ($existing) {
                $this->line("  ⏭️  {$data['name']}: Déjà existe (non écrasée)");
                $skipped++;
                continue;
            }

            // Créer la péripétie
            TwistMission::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'full_text' => $data['full_text'],
                'effect' => $data['effect'],
                'when_drawn' => null,
                'timing' => 'any_battle_round',
                'edition' => $data['edition'],
                'source' => $data['source'],
                'slug' => \Str::slug($data['name']),
                'is_active' => true,
            ]);

            $this->line("  ✅ {$data['name']}: Créée");
            $created++;
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📊 Résultats:');
        $this->info("  ✅ Créées: {$created}");
        $this->info("  ⏭️  Ignorées (existantes): {$skipped}");
        $this->info('═══════════════════════════════════════════════════════════');

        // Proposer de traduire
        $this->info('');
        if ($this->confirm('Voulez-vous traduire les péripéties maintenant?', false)) {
            $this->call('missions:translate-twist', ['--locale' => 'fr']);
        }

        $this->info('✅ Importation terminée !');

        return 0;
    }
}
