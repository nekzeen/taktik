<?php

namespace App\Filament\Resources\TwistMissionResource\Pages;

use App\Filament\Resources\TwistMissionResource;
use App\Models\TwistMission;
use App\Models\Translation;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ImportMissions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = TwistMissionResource::class;

    protected static string $view = 'filament.resources.twist-mission-resource.pages.import-missions';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Importer les péripéties')
                    ->description('Collez le texte des péripéties depuis Wahapedia. Chaque péripétie doit être séparée par "---"')
                    ->schema([
                        Forms\Components\Textarea::make('missions_text')
                            ->label('Texte des péripéties')
                            ->required()
                            ->rows(15)
                            ->placeholder('Collez le texte ici...')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('replace_existing')
                            ->label('Remplacer les péripéties existantes')
                            ->default(false)
                            ->helperText('Si activé, les péripéties existantes avec le même nom seront mises à jour'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('import')
                ->label('Importer les péripéties')
                ->submit('import'),
            Action::make('cancel')
                ->label('Annuler')
                ->url(TwistMissionResource::getUrl('index'))
                ->color('gray'),
        ];
    }

    public function import(): void
    {
        $data = $this->form->getState();
        $text = $data['missions_text'] ?? '';
        $replaceExisting = $data['replace_existing'] ?? false;

        if (empty(trim($text))) {
            Notification::make()
                ->title('Erreur')
                ->body('Veuillez coller le texte des péripéties')
                ->danger()
                ->send();
            return;
        }

        try {
            $missions = $this->parseMissions($text);

            if (empty($missions)) {
                Notification::make()
                    ->title('Erreur')
                    ->body('Aucune péripétie trouvée dans le texte')
                    ->danger()
                    ->send();
                return;
            }

            $count = 0;
            foreach ($missions as $mission) {
                try {
                    $twistMission = TwistMission::updateOrCreate(
                        ['name' => $mission['name']],
                        [
                            'full_text' => $mission['full_text'] ?? '',
                            'slug' => \Str::slug($mission['name']),
                            'edition' => '10ed',
                            'source' => 'chapter-approved-2025-26',
                            'is_active' => true,
                        ]
                    );
                    
                    // Déclencher les traductions automatiques via DeepL
                    $this->triggerAutoTranslations($twistMission);
                    
                    $count++;
                } catch (\Exception $e) {
                    \Log::error("Erreur import péripétie {$mission['name']}: {$e->getMessage()}");
                }
            }

            Notification::make()
                ->title('Succès')
                ->body("{$count} péripéties importées avec succès. Les traductions sont en cours...")
                ->success()
                ->send();

            $this->redirect(TwistMissionResource::getUrl('index'));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur')
                ->body("Erreur lors de l'import: {$e->getMessage()}")
                ->danger()
                ->send();
        }
    }

    protected function triggerAutoTranslations(TwistMission $mission): void
    {
        \Log::info("🔄 Début traduction pour péripétie: {$mission->name} (ID: {$mission->id})");
        
        $translationService = new \App\Services\TranslationService();
        
        // Créer les traductions pour les champs principaux
        $fields = ['name', 'full_text'];
        
        foreach ($fields as $field) {
            try {
                $sourceText = $mission->{$field};
                
                if (empty($sourceText)) {
                    continue;
                }

                // Vérifier si la traduction existe déjà
                $existing = Translation::where('resource_type', 'TwistMission')
                    ->where('resource_id', $mission->id)
                    ->where('field', $field)
                    ->where('locale', 'fr')
                    ->first();

                if ($existing) {
                    continue;
                }

                // Traduire immédiatement via DeepL
                $translatedText = $translationService->translate(
                    $sourceText,
                    'en',
                    'fr'
                );

                // ⚠️ GLOSSAIRE DÉSACTIVÉ
                // Le glossaire n'est plus appliqué automatiquement
                // Les traductions se font uniquement via DeepL

                // Créer la traduction avec statut 'auto' via le modèle
                $translation = Translation::create([
                    'source_text' => $sourceText,
                    'translated_text' => $translatedText,
                    'locale' => 'fr',
                    'resource_type' => 'TwistMission',
                    'resource_id' => $mission->id,
                    'field' => $field,
                    'status' => 'auto',
                ]);
                
                // Forcer la mise à jour pour déclencher l'Observer
                $translation->update(['translated_text' => $translatedText]);
                
            } catch (\Exception $e) {
                \Log::error("Traduction échouée pour {$mission->name} ({$field}): {$e->getMessage()}");
            }
        }
    }

    protected function parseMissions($text)
    {
        $missions = [];
        // Diviser par --- (avec ou sans espaces, au début ou fin de ligne)
        // Accepte: ---, --- , --- \n, \n---\n, etc.
        $blocks = preg_split('/\s*\n\s*-{3,}\s*\n\s*/', $text);

        foreach ($blocks as $block) {
            $block = trim($block);
            if (empty($block)) {
                continue;
            }

            $mission = $this->parseMission($block);
            if ($mission) {
                $missions[] = $mission;
            }
        }

        return $missions;
    }

    protected function parseMission($block)
    {
        // Extraire le titre (entre ** ** ou première ligne)
        $lines = explode("\n", $block);
        $name = null;
        
        // Essayer d'abord le format **Nom**
        if (preg_match('/\*\*([^*]+)\*\*/', $block, $matches)) {
            $name = trim($matches[1]);
        } else {
            // Sinon prendre la première ligne non-vide
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $name = $line;
                    break;
                }
            }
        }

        if (empty($name)) {
            return null;
        }

        // Extraire le texte complet
        $fullText = $block;

        return [
            'name' => $name,
            'full_text' => $fullText,
        ];
    }
}
