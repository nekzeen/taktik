<?php

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use App\Models\ArmyList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArmyListsRelationManager extends RelationManager
{
    protected static string $relationship = 'armyLists';
    
    protected static ?string $title = 'Listes d\'armées';
    
    protected static ?string $modelLabel = 'liste d\'armée';
    
    protected static ?string $pluralModelLabel = 'listes d\'armées';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Joueur')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('points')
                            ->label('Points')
                            ->numeric()
                            ->default(2000)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Liste d\'armée (PDF)')
                    ->description('Uploadez le PDF de la liste d\'armée. Les informations seront extraites automatiquement.')
                    ->schema([
                        Forms\Components\FileUpload::make('pdf_path')
                            ->label('PDF de la liste')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->directory('army-lists')
                            ->disk('public')
                            ->downloadable()
                            ->openable()
                            ->live()
                            ->afterStateUpdated(function (\Filament\Forms\Set $set, $state, \Filament\Forms\Get $get) {
                                if ($state) {
                                    try {
                                        // Le fichier est un objet TemporaryUploadedFile de Livewire
                                        if ($state instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                            $tempPath = $state->getRealPath();
                                            
                                            if (file_exists($tempPath)) {
                                                $analyzer = new \App\Services\ArmyListAnalyzer();
                                                
                                                // Analyser directement depuis le fichier temporaire
                                                $content = file_get_contents($tempPath);
                                                $parser = new \Smalot\PdfParser\Parser();
                                                $pdf = $parser->parseContent($content);
                                                $text = $pdf->getText();
                                                
                                                // Détecter la faction
                                                $faction = $analyzer->detectFaction($text);
                                                if ($faction) {
                                                    $set('faction_id', $faction->id);
                                                }
                                                
                                                // Détecter le détachement
                                                $detachment = $analyzer->detectDetachment($text);
                                                if ($detachment) {
                                                    $set('detachment', $detachment);
                                                }
                                                
                                                // Détecter les points
                                                $points = $analyzer->detectPoints($text);
                                                if ($points && !$get('points')) {
                                                    $set('points', $points);
                                                }

                                                // Notification
                                                \Filament\Notifications\Notification::make()
                                                    ->success()
                                                    ->title('PDF analysé')
                                                    ->body($faction ? "Faction détectée : {$faction->name}" : 'Analyse terminée')
                                                    ->send();
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        \Log::error('Erreur analyse PDF: ' . $e->getMessage());
                                        \Filament\Notifications\Notification::make()
                                            ->warning()
                                            ->title('Analyse partielle')
                                            ->body('Le PDF a été uploadé mais certaines informations n\'ont pas pu être extraites.')
                                            ->send();
                                    }
                                }
                            })
                            ->helperText('Format PDF uniquement, taille maximale 10 Mo. Les informations seront extraites automatiquement.'),
                    ]),

                Forms\Components\Section::make('Détails de l\'armée')
                    ->description('Ces informations seront remplies automatiquement après l\'upload du PDF')
                    ->schema([
                        Forms\Components\Select::make('faction_id')
                            ->label('Faction')
                            ->relationship('faction', 'name')
                            ->searchable()
                            ->nullable()
                            ->helperText('Détectée automatiquement depuis le PDF'),
                        Forms\Components\TextInput::make('detachment')
                            ->label('Détachement')
                            ->maxLength(255)
                            ->nullable()
                            ->helperText('Détecté automatiquement depuis le PDF'),
                    ])->columns(2),

                Forms\Components\Section::make('Validation')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'draft' => 'Brouillon',
                                'pending' => 'En attente',
                                'validated' => 'Validée',
                                'rejected' => 'Rejetée',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Raison du rejet')
                            ->columnSpanFull()
                            ->rows(3)
                            ->visible(fn (Forms\Get $get) => $get('status') === 'rejected'),
                    ])->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Joueur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('faction.name')
                    ->label('Faction')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('detachment')
                    ->label('Détachement')
                    ->searchable()
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'pending' => 'En attente',
                        'validated' => 'Validée',
                        'rejected' => 'Rejetée',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'validated' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('pdf_path')
                    ->label('PDF')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document-minus')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('validated_at')
                    ->label('Validée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('validator.name')
                    ->label('Validée par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'pending' => 'En attente',
                        'validated' => 'Validée',
                        'rejected' => 'Rejetée',
                    ]),
                Tables\Filters\SelectFilter::make('faction')
                    ->label('Faction')
                    ->relationship('faction', 'name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Ajouter une liste')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Calculer le hash du PDF si présent
                        if (isset($data['pdf_path'])) {
                            $fullPath = storage_path('app/public/' . $data['pdf_path']);
                            if (file_exists($fullPath)) {
                                $data['pdf_hash'] = hash_file('sha256', $fullPath);
                                $data['pdf_size'] = filesize($fullPath);
                            }
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('validate')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (ArmyList $record) {
                        $record->update([
                            'status' => 'validated',
                            'validated_at' => now(),
                            'validated_by' => auth()->id(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Liste validée')
                            ->body("La liste de {$record->user->name} a été validée.")
                            ->send();
                    })
                    ->visible(fn (ArmyList $record) => $record->status === 'pending'),
                
                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Raison du rejet')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (ArmyList $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Liste rejetée')
                            ->body("La liste de {$record->user->name} a été rejetée.")
                            ->send();
                    })
                    ->visible(fn (ArmyList $record) => $record->status === 'pending'),
                
                Tables\Actions\Action::make('reload_pdf')
                    ->label('Recharger les infos')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (ArmyList $record) {
                        if (!$record->pdf_path) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Aucun PDF')
                                ->body('Aucun PDF n\'a été trouvé pour cette liste.')
                                ->send();
                            return;
                        }

                        try {
                            $fullPath = storage_path('app/public/' . $record->pdf_path);
                            
                            if (!file_exists($fullPath)) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Fichier introuvable')
                                    ->body('Le fichier PDF n\'existe pas sur le serveur.')
                                    ->send();
                                return;
                            }

                            $analyzer = new \App\Services\ArmyListAnalyzer();
                            $content = file_get_contents($fullPath);
                            $parser = new \Smalot\PdfParser\Parser();
                            $pdf = $parser->parseContent($content);
                            $text = $pdf->getText();

                            $updated = false;
                            $details = [];

                            // Détecter la faction
                            $faction = $analyzer->detectFaction($text);
                            if ($faction && $record->faction_id !== $faction->id) {
                                $record->update(['faction_id' => $faction->id]);
                                $details[] = "Faction: {$faction->name}";
                                $updated = true;
                            }

                            // Détecter le détachement
                            $detachment = $analyzer->detectDetachment($text);
                            if ($detachment && $record->detachment !== $detachment) {
                                $record->update(['detachment' => $detachment]);
                                $details[] = "Détachement: {$detachment}";
                                $updated = true;
                            }

                            // Détecter les points
                            $points = $analyzer->detectPoints($text);
                            if ($points && $record->points !== $points) {
                                $record->update(['points' => $points]);
                                $details[] = "Points: {$points}";
                                $updated = true;
                            }

                            if ($updated) {
                                \Filament\Notifications\Notification::make()
                                    ->success()
                                    ->title('Informations rechargées')
                                    ->body('Modifications: ' . implode(', ', $details))
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->info()
                                    ->title('Aucune modification')
                                    ->body('Les informations sont déjà à jour.')
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            \Log::error('Erreur recharge PDF: ' . $e->getMessage());
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Erreur lors du recharge')
                                ->body('Une erreur est survenue: ' . $e->getMessage())
                                ->send();
                        }
                    })
                    ->visible(fn (ArmyList $record) => $record->pdf_path !== null),

                Tables\Actions\Action::make('download')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (ArmyList $record) => $record->pdf_path ? \Storage::disk('public')->url($record->pdf_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (ArmyList $record) => $record->pdf_path !== null),
                
                Tables\Actions\EditAction::make()
                    ->label('Modifier')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Calculer le hash du PDF si présent et modifié
                        if (isset($data['pdf_path'])) {
                            $fullPath = storage_path('app/public/' . $data['pdf_path']);
                            if (file_exists($fullPath)) {
                                $data['pdf_hash'] = hash_file('sha256', $fullPath);
                                $data['pdf_size'] = filesize($fullPath);
                            }
                        }
                        return $data;
                    }),
                    
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('validate_all')
                        ->label('Valider toutes')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function (ArmyList $record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'validated',
                                        'validated_at' => now(),
                                        'validated_by' => auth()->id(),
                                    ]);
                                }
                            });
                            
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Listes validées')
                                ->body("{$records->count()} liste(s) ont été validées.")
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
