<?php

namespace App\Filament\Resources\ArmyListResource\Pages;

use App\Filament\Resources\ArmyListResource;
use App\Services\ArmyListAnalyzer;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateArmyList extends CreateRecord
{
    protected static string $resource = ArmyListResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si un PDF a été uploadé, l'analyser
        if (isset($data['pdf_path']) && $data['pdf_path']) {
            try {
                $analyzer = new ArmyListAnalyzer();
                $analysis = $analyzer->analyze($data['pdf_path']);

                // Remplir automatiquement les champs si détectés
                if (isset($analysis['faction_id']) && !$data['faction_id']) {
                    $data['faction_id'] = $analysis['faction_id'];
                }

                // Déterminer l'ID du détachement si disponible
                if (isset($analysis['faction_id']) && !isset($data['detachment_id'])) {
                    $detachmentId = $analyzer->detectDetachmentId($analysis['raw_text'] ?? '');
                    if ($detachmentId) {
                        $data['detachment_id'] = $detachmentId;
                    }
                }

                if (isset($analysis['points']) && !$data['points']) {
                    $data['points'] = $analysis['points'];
                }

                // Calculer le hash du PDF
                $pdfContent = \Storage::disk('private')->get($data['pdf_path']);
                $data['pdf_hash'] = hash('sha256', $pdfContent);
                $data['pdf_size'] = strlen($pdfContent);

                // Notification de succès
                if (isset($analysis['faction_name'])) {
                    Notification::make()
                        ->success()
                        ->title('PDF analysé avec succès')
                        ->body("Faction détectée : {$analysis['faction_name']}")
                        ->send();
                }
            } catch (\Exception $e) {
                Notification::make()
                    ->warning()
                    ->title('Analyse partielle du PDF')
                    ->body('Le PDF a été uploadé mais certaines informations n\'ont pas pu être extraites automatiquement.')
                    ->send();
            }
        }

        return $data;
    }
}
