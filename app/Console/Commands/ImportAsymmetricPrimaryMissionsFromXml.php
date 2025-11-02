<?php

namespace App\Console\Commands;

use App\Models\AsymmetricPrimaryMission;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportAsymmetricPrimaryMissionsFromXml extends Command
{
    protected $signature = 'missions:import-asymmetric-xml {file? : Path to XML file} {--source=chapter-approved-2025-26}';
    protected $description = 'Importer les missions primaires asymétriques depuis un fichier XML';

    public function handle()
    {
        $file = $this->argument('file') ?? storage_path('missions/asymmetric-primary-missions-chapter-approved-2025-26.xml');
        $source = $this->option('source');

        if (!file_exists($file)) {
            $this->error("❌ Fichier non trouvé: {$file}");
            return 1;
        }

        $this->info('📥 Importation des missions primaires asymétriques depuis XML...');
        $this->info('═══════════════════════════════════════════════════════════');

        try {
            $xml = simplexml_load_file($file);
            $missions = $xml->mission;

            if (empty($missions)) {
                $this->warn('⚠️  Aucune mission trouvée dans le fichier XML !');
                return 1;
            }

            $created = 0;
            $updated = 0;

            foreach ($missions as $missionXml) {
                try {
                    $title = (string) $missionXml->title;
                    $description = (string) $missionXml->description;
                    $full_text = (string) $missionXml->full_text;
                    $objectives = (string) $missionXml->objectives;
                    $attacker_objective = (string) $missionXml->attacker_objective;
                    $defender_objective = (string) $missionXml->defender_objective;
                    $timing = (string) $missionXml->timing;
                    $scoring = (string) $missionXml->scoring;
                    $max_vp = (int) $missionXml->max_vp;

                    // Créer ou mettre à jour la mission
                    $mission = AsymmetricPrimaryMission::updateOrCreate(
                        ['name' => $title, 'source' => $source],
                        [
                            'description' => $description ?: 'No description',
                            'full_text' => $full_text ?: $this->buildFullText($title, $description, $objectives),
                            'objectives' => $objectives,
                            'attacker_objective' => $attacker_objective,
                            'defender_objective' => $defender_objective,
                            'timing' => $timing,
                            'scoring_conditions' => $scoring,
                            'max_vp' => $max_vp,
                            'slug' => Str::slug($title),
                            'edition' => '10ed',
                            'source' => $source,
                            'is_active' => true,
                        ]
                    );

                    if ($mission->wasRecentlyCreated) {
                        $this->line("  ✅ Créée: {$title}");
                        $created++;
                    } else {
                        $this->line("  🔄 Mise à jour: {$title}");
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $this->error("  ❌ Erreur pour {$title}: {$e->getMessage()}");
                }
            }

            $this->info('');
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info("📊 Résultats:");
            $this->info("  ✅ Créées: {$created}");
            $this->info("  🔄 Mises à jour: {$updated}");
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('✅ Importation terminée !');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            return 1;
        }
    }

    protected function buildFullText(string $title, string $description, string $objectives): string
    {
        $text = "Asymmetric Primary Mission\n";
        $text .= "{$title}\n";
        if ($description) {
            $text .= "{$description}\n\n";
        }
        if ($objectives) {
            $text .= "OBJECTIVES: {$objectives}\n";
        }
        return $text;
    }
}
