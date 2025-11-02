<?php

namespace App\Console\Commands;

use App\Models\TwistMission;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportTwistMissionsFromXml extends Command
{
    protected $signature = 'missions:import-twist-xml {file? : Path to XML file} {--source=chapter-approved-2025-26}';
    protected $description = 'Importer les péripéties depuis un fichier XML';

    public function handle()
    {
        $file = $this->argument('file') ?? storage_path('missions/twist-missions-chapter-approved-2025-26.xml');
        $source = $this->option('source');

        if (!file_exists($file)) {
            $this->error("❌ Fichier non trouvé: {$file}");
            return 1;
        }

        $this->info('📥 Importation des péripéties depuis XML...');
        $this->info('═══════════════════════════════════════════════════════════');

        try {
            $xml = simplexml_load_file($file);
            $missions = $xml->mission;

            if (empty($missions)) {
                $this->warn('⚠️  Aucune péripétie trouvée dans le fichier XML !');
                return 1;
            }

            $created = 0;
            $updated = 0;

            foreach ($missions as $missionXml) {
                try {
                    $title = (string) $missionXml->title;
                    $flavour = (string) $missionXml->flavour;
                    $when_drawn = (string) $missionXml->when_drawn;
                    $effect = (string) $missionXml->effect;
                    $notes = (string) $missionXml->notes;

                    // Créer ou mettre à jour la péripétie
                    $mission = TwistMission::updateOrCreate(
                        ['name' => $title, 'source' => $source],
                        [
                            'description' => $flavour ?: 'No description',
                            'full_text' => $this->buildFullText($title, $flavour, $when_drawn, $effect),
                            'when_drawn' => $when_drawn,
                            'effect' => $effect,
                            'timing' => 'any_battle_round',
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

    protected function buildFullText(string $title, string $flavour, string $when_drawn, string $effect): string
    {
        $text = "Twist Card\n";
        $text .= "{$title}\n";
        if ($flavour) {
            $text .= "{$flavour}\n\n";
        }
        if ($when_drawn) {
            $text .= "WHEN DRAWN: {$when_drawn}\n\n";
        }
        if ($effect) {
            $text .= "EFFECT: {$effect}\n";
        }
        return $text;
    }
}
