<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\TranslationService;

class CreateStratagemsTranslations extends Command
{
    protected $signature = 'stratagems:create-translations {--force}';
    protected $description = 'Créer les traductions pour les stratagèmes dans la table translations';

    private $translationService;

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    public function handle()
    {
        $this->info('🚀 Création des traductions pour les stratagèmes...');

        try {
            $force = $this->option('force');
            $stratagems = DB::table('stratagems')->get();
            $created = 0;
            $skipped = 0;
            $errors = 0;

            foreach ($stratagems as $stratagem) {
                // Vérifier si la traduction existe déjà
                $exists = DB::table('translations')
                    ->where('resource_type', 'Stratagem')
                    ->where('resource_id', $stratagem->id)
                    ->where('field', 'name')
                    ->where('locale', 'fr')
                    ->exists();

                if ($exists && !$force) {
                    $skipped++;
                    continue;
                }

                try {
                    // Traduire le nom
                    $nameFr = $this->translationService->translate(
                        $stratagem->name_en,
                        'en',
                        'fr',
                        'deepl'
                    );

                    // Créer ou mettre à jour la traduction
                    DB::table('translations')->updateOrInsert(
                        [
                            'resource_type' => 'Stratagem',
                            'resource_id' => $stratagem->id,
                            'field' => 'name',
                            'locale' => 'fr',
                        ],
                        [
                            'source_text' => $stratagem->name_en,
                            'translated_text' => $nameFr,
                            'status' => 'auto',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    $created++;
                    $this->line("  ✅ {$stratagem->name_en} → {$nameFr}");

                    // Délai pour respecter les limites de l'API DeepL
                    usleep(300000); // 0.3 secondes

                } catch (\Exception $e) {
                    $errors++;
                    $this->warn("  ❌ {$stratagem->name_en} : {$e->getMessage()}");
                }
            }

            $this->info("\n✅ Traductions créées : $created");
            $this->info("⏭️  Traductions existantes : $skipped");
            $this->info("❌ Erreurs : $errors");
            $this->info("✅ Terminé !");

        } catch (\Exception $e) {
            $this->error("❌ Erreur : {$e->getMessage()}");
        }
    }
}
