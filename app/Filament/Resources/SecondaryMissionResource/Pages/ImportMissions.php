<?php

namespace App\Filament\Resources\SecondaryMissionResource\Pages;

use App\Filament\Resources\SecondaryMissionResource;
use App\Models\SecondaryMission;
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

    protected static string $resource = SecondaryMissionResource::class;

    protected static string $view = 'filament.resources.secondary-mission-resource.pages.import-missions';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Importer les missions secondaires')
                    ->description('Collez le texte des missions depuis Wahapedia. Chaque mission doit être séparée par "---"')
                    ->schema([
                        Forms\Components\Textarea::make('missions_text')
                            ->label('Texte des missions')
                            ->required()
                            ->rows(15)
                            ->placeholder('Collez le texte ici...')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('replace_existing')
                            ->label('Remplacer les missions existantes')
                            ->default(false)
                            ->helperText('Si activé, les missions existantes avec le même nom seront mises à jour'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('import')
                ->label('Importer les missions')
                ->submit('import'),
            Action::make('cancel')
                ->label('Annuler')
                ->url(SecondaryMissionResource::getUrl('index'))
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
                ->body('Veuillez coller le texte des missions')
                ->danger()
                ->send();
            return;
        }

        try {
            $missions = $this->parseMissions($text);

            if (empty($missions)) {
                Notification::make()
                    ->title('Erreur')
                    ->body('Aucune mission trouvée dans le texte')
                    ->danger()
                    ->send();
                return;
            }

            $count = 0;
            foreach ($missions as $mission) {
                try {
                    $secondaryMission = SecondaryMission::updateOrCreate(
                        ['name' => $mission['name']],
                        [
                            'description' => $mission['description'] ?? '',
                            'full_text' => $mission['full_text'] ?? '',
                            'when_drawn' => $mission['when_drawn'] ?? '',
                            'when_condition' => $mission['when_condition'] ?? '',
                            'scoring_conditions' => json_encode($mission['scoring_items'] ?? []),
                            'max_vp' => $mission['max_vp'] ?? 15,
                            'slug' => \Str::slug($mission['name']),
                            'edition' => '10ed',
                            'source' => 'chapter-approved-2025-26',
                            'is_active' => true,
                        ]
                    );
                    
                    // Déclencher les traductions automatiques via DeepL
                    $this->triggerAutoTranslations($secondaryMission);
                    
                    $count++;
                } catch (\Exception $e) {
                    \Log::error("Erreur import mission {$mission['name']}: {$e->getMessage()}");
                }
            }

            Notification::make()
                ->title('Succès')
                ->body("{$count} missions importées avec succès. Les traductions sont en cours...")
                ->success()
                ->send();

            $this->redirect(SecondaryMissionResource::getUrl('index'));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur')
                ->body("Erreur lors de l'import: {$e->getMessage()}")
                ->danger()
                ->send();
        }
    }

    protected function triggerAutoTranslations(SecondaryMission $mission): void
    {
        \Log::info("🔄 Début traduction pour mission: {$mission->name} (ID: {$mission->id})");
        
        $translationService = new \App\Services\TranslationService();
        $intelligentService = new \App\Services\IntelligentTranslationService();
        
        // Créer les traductions pour les champs principaux
        $fields = ['name', 'description', 'full_text', 'when_drawn', 'when_condition'];
        
        foreach ($fields as $field) {
            try {
                $sourceText = $mission->{$field};
                
                if (empty($sourceText)) {
                    continue;
                }

                // Vérifier si la traduction existe déjà
                $existing = Translation::where('resource_type', 'SecondaryMission')
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

                // Appliquer les termes du glossaire Warhammer
                $translatedText = $intelligentService->translateWithGlossary(
                    $translatedText,
                    'fr',
                    'secondary_mission',
                    $sourceText
                );

                // Créer la traduction avec statut 'auto' via le modèle
                $translation = Translation::create([
                    'source_text' => $sourceText,
                    'translated_text' => $translatedText,
                    'locale' => 'fr',
                    'resource_type' => 'SecondaryMission',
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
        $blocks = preg_split('/^---\s*$/m', $text);

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
        // Extraire le titre (entre ** **)
        if (!preg_match('/\*\*([^*]+)\*\*/', $block, $matches)) {
            return null;
        }

        $name = trim($matches[1]);

        // Extraire la description (première ligne après le titre)
        $lines = explode("\n", $block);
        $description = '';
        $foundTitle = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '**') !== false) {
                $foundTitle = true;
                continue;
            }
            if ($foundTitle && !empty($line) && $line !== 'When Drawn:' && !preg_match('/^(ANY|SECOND)/', $line)) {
                $description = $line;
                break;
            }
        }

        // Extraire "When Drawn"
        $whenDrawn = '';
        if (preg_match('/When Drawn:\s*(.+?)(?=\n\n|ANY BATTLE|SECOND BATTLE|$)/s', $block, $matches)) {
            $whenDrawn = trim($matches[1]);
        }

        // Extraire le texte complet
        $fullText = $block;

        // Extraire les conditions de scoring
        $scoringItems = [];
        if (preg_match_all('/^([^:]+?):\s*(.+?)(?=\n(?:[A-Z]|$))/m', $block, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $condition = trim($match[1]);
                $vp = trim($match[2]);
                if (!empty($condition) && !empty($vp)) {
                    $scoringItems[] = "{$condition}: {$vp}";
                }
            }
        }

        // Calculer max VP
        $maxVp = 15;
        if (preg_match_all('/(\d+)VP/', $block, $matches)) {
            $vps = array_map('intval', $matches[1]);
            $maxVp = max($vps);
        }

        return [
            'name' => $name,
            'description' => $description,
            'full_text' => $fullText,
            'when_drawn' => $whenDrawn,
            'when_condition' => 'ANY BATTLE ROUND',
            'scoring_items' => $scoringItems,
            'max_vp' => $maxVp,
        ];
    }
}
