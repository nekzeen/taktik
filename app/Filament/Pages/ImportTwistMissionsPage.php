<?php

namespace App\Filament\Pages;

use App\Models\TwistMission;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ImportTwistMissionsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Importer Péripéties';
    protected static ?string $title = 'Importer les Péripéties';
    protected static ?string $navigationGroup = 'Warhammer 40k';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.import-twist-missions-page';

    public ?array $data = [
        'full_text' => '',
        'name' => '',
    ];

    public function mount(): void
    {
        $this->form->fill($this->data);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Importer les Péripéties')
                ->description('Copiez le texte complet d\'une péripétie depuis Wahapedia et collez-le ci-dessous')
                ->schema([
                    Textarea::make('full_text')
                        ->label('Texte complet de la péripétie')
                        ->placeholder('Collez le texte complet ici...')
                        ->rows(10)
                        ->required(),

                    TextInput::make('name')
                        ->label('Nom (optionnel - sera extrait automatiquement)')
                        ->placeholder('Sera extrait de la première ligne'),
                ]),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('import')
                ->label('Importer cette péripétie')
                ->action('import'),

            Action::make('importMultiple')
                ->label('Importer plusieurs péripéties')
                ->action('importMultiple'),
        ];
    }

    public function import(): void
    {
        try {
            // Récupérer les données du formulaire
            $fullText = trim($this->data['full_text'] ?? '');
            
            if (empty($fullText)) {
                Notification::make()
                    ->danger()
                    ->title('Erreur')
                    ->body('Veuillez entrer le texte complet')
                    ->send();
                return;
            }

            // Extraire le nom
            $lines = explode("\n", $fullText);
            $name = !empty($this->data['name']) ? trim($this->data['name']) : trim($lines[0]);

            // Vérifier que le nom est valide
            if (strlen($name) < 3) {
                Notification::make()
                    ->danger()
                    ->title('Erreur')
                    ->body('Le nom doit contenir au moins 3 caractères')
                    ->send();
                return;
            }

            // Vérifier que la péripétie n'existe pas déjà
            $existing = TwistMission::where('name', $name)
                ->where('source', 'chapter-approved-2025-26')
                ->first();

            if ($existing) {
                Notification::make()
                    ->warning()
                    ->title('Avertissement')
                    ->body("La péripétie '{$name}' existe déjà et ne sera pas écrasée")
                    ->send();
                return;
            }

            // Extraire la description et l'effet
            $description = isset($lines[1]) ? trim($lines[1]) : '';
            $effectLines = array_slice($lines, 2);
            $effect = implode("\n", $effectLines);

            // Créer la péripétie
            TwistMission::create([
                'name' => $name,
                'description' => $description,
                'full_text' => $fullText,
                'effect' => $effect,
                'when_drawn' => null,
                'timing' => 'any_battle_round',
                'edition' => 'Chapter Approved 2025-26',
                'source' => 'chapter-approved-2025-26',
                'slug' => \Str::slug($name),
                'is_active' => true,
            ]);

            // Réinitialiser le formulaire
            $this->data = [];
            $this->form->fill();

            Notification::make()
                ->success()
                ->title('Succès')
                ->body("Péripétie '{$name}' importée avec succès !")
                ->send();
        } catch (\Exception $e) {
            \Log::error('Import error: ' . $e->getMessage(), ['exception' => $e]);
            Notification::make()
                ->danger()
                ->title('Erreur')
                ->body('Erreur lors de l\'import: ' . $e->getMessage())
                ->send();
        }
    }

    public function importMultiple(): void
    {
        Notification::make()
            ->info()
            ->title('Information')
            ->body('Utilisez la commande: php artisan twist:import-interactive')
            ->send();
    }
}
