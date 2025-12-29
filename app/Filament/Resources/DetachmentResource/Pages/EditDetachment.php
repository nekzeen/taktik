<?php

namespace App\Filament\Resources\DetachmentResource\Pages;

use App\Filament\Resources\DetachmentResource;
use App\Models\Detachment;
use App\Models\Translation;
use App\Services\TranslationService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDetachment extends EditRecord
{
    protected static string $resource = DetachmentResource::class;

    protected function afterSave(): void
    {
        /** @var Detachment $detachment */
        $detachment = $this->getRecord();

        $this->triggerAutoTranslations($detachment);
    }

    protected function triggerAutoTranslations(Detachment $detachment): void
    {
        $translationService = new TranslationService();
        $fields = ['name', 'description'];

        foreach ($fields as $field) {
            try {
                $sourceText = trim((string) $detachment->{$field});

                if ($sourceText === '') {
                    continue;
                }

                $existing = Translation::where('resource_type', 'Detachment')
                    ->where('resource_id', $detachment->id)
                    ->where('field', $field)
                    ->where('locale', 'fr')
                    ->first();

                if ($existing) {
                    continue;
                }

                $translatedText = $translationService->translate(
                    $sourceText,
                    'en',
                    'fr'
                );

                Translation::create([
                    'source_text' => $sourceText,
                    'translated_text' => $translatedText,
                    'locale' => 'fr',
                    'resource_type' => 'Detachment',
                    'resource_id' => $detachment->id,
                    'field' => $field,
                    'status' => 'auto',
                ]);
            } catch (\Exception $e) {
                \Log::error("Traduction échouée pour Detachment {$detachment->id} ({$field}): {$e->getMessage()}");
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
