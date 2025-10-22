<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TournamentResource\Pages;
use App\Filament\Resources\TournamentResource\RelationManagers;
use App\Models\Tournament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TournamentResource extends Resource
{
    protected static ?string $model = Tournament::class;

    public static function canCreate(): bool
    {
        $user = auth()->user();
        return $user->can('create', Tournament::class);
    }

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    
    protected static ?string $navigationLabel = 'Tournois';
    
    protected static ?string $modelLabel = 'tournoi';
    
    protected static ?string $pluralModelLabel = 'tournois';
    
    protected static ?string $navigationGroup = 'Gestion des tournois';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole(['super-admin', 'admin']);
        
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                Forms\Components\Select::make('format')
                    ->label('Format')
                    ->options([
                        'elimination' => 'Élimination',
                        'swiss' => 'Suisse',
                        'league' => 'Ligue',
                    ])
                    ->default('elimination')
                    ->required(),
                Forms\Components\Select::make('army_size')
                    ->label('Taille d\'armée')
                    ->options([
                        'incursion' => 'INCURSION (1000 pts)',
                        'strike_force' => 'FORCE DE FRAPPE (2000 pts)',
                        'onslaught' => 'OFFENSIVE (3000 pts)',
                    ])
                    ->default('strike_force')
                    ->required()
                    ->helperText('Définit le nombre de points d\'armée pour les parties de ce tournoi'),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Date de début'),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Date de fin'),
                Forms\Components\DateTimePicker::make('registration_deadline')
                    ->label('Date limite d\'inscription'),
                Forms\Components\TextInput::make('max_players')
                    ->label('Nombre maximum de joueurs')
                    ->numeric()
                    ->default(null),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'open' => 'Ouvert',
                        'registration_closed' => 'Inscriptions fermées',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                    ])
                    ->default('draft')
                    ->required()
                    ->visible($isAdmin),
                Forms\Components\DateTimePicker::make('bracket_generated_at')
                    ->label('Bracket généré le')
                    ->disabled()
                    ->visible($isAdmin),
                Forms\Components\Select::make('created_by')
                    ->label('Créé par')
                    ->relationship('creator', 'name')
                    ->default(fn () => auth()->id())
                    ->required()
                    ->visible($isAdmin),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('format')
                    ->label('Format')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'elimination' => 'Élimination',
                        'swiss' => 'Suisse',
                        'league' => 'Ligue',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'elimination' => 'danger',
                        'swiss' => 'warning',
                        'league' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('army_size')
                    ->label('Taille')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'incursion' => 'INCURSION (1000 pts)',
                        'strike_force' => 'FORCE DE FRAPPE (2000 pts)',
                        'onslaught' => 'OFFENSIVE (3000 pts)',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'incursion' => 'info',
                        'strike_force' => 'success',
                        'onslaught' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'open' => 'Ouvert',
                        'registration_closed' => 'Inscriptions fermées',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'open' => 'success',
                        'registration_closed' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Date de début')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Date de fin')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_players')
                    ->label('Max joueurs')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Créé par')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            RelationManagers\ArmyListsRelationManager::class,
            RelationManagers\TournamentMatchesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTournaments::route('/'),
            'create' => Pages\CreateTournament::route('/create'),
            'edit' => Pages\EditTournament::route('/{record}/edit'),
        ];
    }
}
