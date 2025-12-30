<?php

namespace App\Console\Commands;

use App\Models\Detachment;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Console\Command;

class TranslateMissingDetachmentTranslations extends Command
{
    protected $signature = 'detachments:translate-missing {--force : Recréer les traductions même si elles existent} {--translator=deepl : Traducteur à utiliser (deepl ou google)}';

    protected $description = 'Crée les traductions FR manquantes pour les détachements (name/description).';

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $translator = (string) $this->option('translator');
        if ($translator === '') {
            $translator = (string) config('translation.default', 'deepl');
        }

        $service = new TranslationService();

        $detachments = Detachment::query()->get();
        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($detachments as $detachment) {
            foreach (['name', 'description'] as $field) {
                try {
                    $sourceText = trim((string) $detachment->{$field});
                    if ($sourceText === '') {
                        $skipped++;
                        continue;
                    }

                    $existing = Translation::where('resource_type', 'Detachment')
                        ->where('resource_id', $detachment->id)
                        ->where('field', $field)
                        ->where('locale', 'fr')
                        ->first();

                    if ($existing && !$force) {
                        $skipped++;
                        continue;
                    }

                    $translatedText = $service->translate($sourceText, 'en', 'fr', $translator);

                    $status = $translatedText === $sourceText ? 'pending' : 'auto';

                    Translation::updateOrCreate(
                        [
                            'resource_type' => 'Detachment',
                            'resource_id' => $detachment->id,
                            'field' => $field,
                            'locale' => 'fr',
                        ],
                        [
                            'source_text' => $sourceText,
                            'translated_text' => $translatedText,
                            'status' => $status,
                        ]
                    );

                    $created++;
                } catch (\Throwable $e) {
                    $errors++;
                    $this->error("Detachment {$detachment->id} ({$field}) : {$e->getMessage()}");
                }
            }
        }

        $this->info("Terminé. Créées/MAJ: {$created}. Ignorées: {$skipped}. Erreurs: {$errors}.");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
