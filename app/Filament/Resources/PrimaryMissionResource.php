<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrimaryMissionResource\Pages;
use App\Filament\Resources\PrimaryMissionResource\RelationManagers;
use App\Models\PrimaryMission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class PrimaryMissionResource extends Resource
{
    protected static ?string $model = PrimaryMission::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Missions Primaires';

    protected static ?string $modelLabel = 'mission primaire';

    protected static ?string $pluralModelLabel = 'missions primaires';

    protected static ?string $navigationGroup = 'Warhammer 40k';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Données de base')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de la mission (EN)')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->label('Description courte (EN)')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Texte complet')
                    ->schema([
                        Forms\Components\Textarea::make('full_text')
                            ->label('Texte complet de la mission (EN)')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Timing')
                    ->schema([
                        Forms\Components\Select::make('timing')
                            ->label('Timing')
                            ->options([
                                'second_battle_round_onwards' => 'À partir du 2e round de bataille',
                                'from_battle_round_two' => 'À partir du round 2',
                                'any_battle_round' => 'N\'importe quel round',
                            ])
                            ->default('second_battle_round_onwards')
                            ->required(),
                        Forms\Components\Textarea::make('when_condition')
                            ->label('Condition de timing (EN)')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Scoring')
                    ->schema([
                        Forms\Components\TextInput::make('max_vp')
                            ->label('Points de victoire maximum par tour')
                            ->numeric()
                            ->default(15)
                            ->required(),
                        Forms\Components\Textarea::make('scoring_conditions')
                            ->label('Conditions de scoring (JSON)')
                            ->required()
                            ->rows(4)
                            ->helperText('Format JSON avec les conditions de scoring')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Métadonnées')
                    ->schema([
                        Forms\Components\Select::make('edition')
                            ->label('Édition')
                            ->options([
                                '10ed' => 'Warhammer 40k 10e édition',
                            ])
                            ->default('10ed')
                            ->required(),
                        Forms\Components\TextInput::make('source')
                            ->label('Source')
                            ->default('chapter-approved-2025-26')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom (EN)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_fr')
                    ->label('Nom (FR)')
                    ->getStateUsing(function (PrimaryMission $record) {
                        $translation = DB::table('translations')
                            ->where('resource_type', 'PrimaryMission')
                            ->where('resource_id', $record->id)
                            ->where('field', 'name')
                            ->where('locale', 'fr')
                            ->first();
                        return $translation?->translated_text ?? '—';
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        return $query->whereHas('translations', function (Builder $q) use ($search) {
                            $q->where('field', 'name')
                              ->where('locale', 'fr')
                              ->where('translated_text', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction) {
                        return $query->leftJoin('translations', function ($join) {
                            $join->on('primary_missions.id', '=', 'translations.resource_id')
                                 ->where('translations.resource_type', 'PrimaryMission')
                                 ->where('translations.field', 'name')
                                 ->where('translations.locale', 'fr');
                        })->orderBy('translations.translated_text', $direction);
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('max_vp')
                    ->label('Max VP')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'chapter-approved-2025-26' => 'Chapter Approved 2025-26',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\SectionsRelationManager::class,
            RelationManagers\TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrimaryMissions::route('/'),
            'create' => Pages\CreatePrimaryMission::route('/create'),
            'edit' => Pages\EditPrimaryMission::route('/{record}/edit'),
        ];
    }
}
