<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArmyListResource\Pages;
use App\Filament\Resources\ArmyListResource\RelationManagers;
use App\Models\ArmyList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArmyListResource extends Resource
{
    protected static ?string $model = ArmyList::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Listes d\'armées';
    
    protected static ?string $modelLabel = 'liste d\'armée';
    
    protected static ?string $pluralModelLabel = 'listes d\'armées';
    
    protected static ?string $navigationGroup = 'Gestion des tournois';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
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
                        Forms\Components\Select::make('tournament_id')
                            ->label('Tournoi')
                            ->relationship('tournament', 'name')
                            ->searchable()
                            ->nullable(),
                        Forms\Components\TextInput::make('points')
                            ->label('Points')
                            ->numeric()
                            ->default(2000)
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Liste d\'armée (PDF)')
                    ->description('Uploadez le PDF de votre liste d\'armée. Les informations seront extraites automatiquement.')
                    ->schema([
                        Forms\Components\FileUpload::make('pdf_path')
                            ->label('PDF de la liste')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->directory('army-lists')
                            ->disk('private')
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
                            ->helperText('Format PDF uniquement, taille maximale 5 Mo. Les informations seront extraites automatiquement.'),
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
                            ->default('draft')
                            ->required(),
                        Forms\Components\DateTimePicker::make('validated_at')
                            ->label('Validée le')
                            ->disabled(),
                        Forms\Components\Select::make('validated_by')
                            ->label('Validée par')
                            ->relationship('validator', 'name')
                            ->disabled(),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Raison du rejet')
                            ->columnSpanFull()
                            ->rows(3),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Joueur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tournament.name')
                    ->label('Tournoi')
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
                    ->limit(30),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
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
                    })
                    ->visible(fn (ArmyList $record) => $record->status === 'pending'),
                
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('download')
                    ->label('Télécharger PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (ArmyList $record) => $record->pdf_path ? \Storage::disk('private')->url($record->pdf_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (ArmyList $record) => $record->pdf_path !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArmyLists::route('/'),
            'create' => Pages\CreateArmyList::route('/create'),
            'edit' => Pages\EditArmyList::route('/{record}/edit'),
        ];
    }
}
