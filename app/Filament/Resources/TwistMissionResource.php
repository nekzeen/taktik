<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TwistMissionResource\Pages;
use App\Filament\Resources\TwistMissionResource\RelationManagers;
use App\Models\TwistMission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class TwistMissionResource extends Resource
{
    protected static ?string $model = TwistMission::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Warhammer 40k';
    protected static ?string $navigationLabel = 'Péripéties';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Données de base')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de la péripétie (EN)')
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
                            ->label('Texte complet de la péripétie (EN)')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Conditions et Effets')
                    ->schema([
                        Forms\Components\Textarea::make('when_drawn')
                            ->label('Condition "When Drawn" (EN)')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('effect')
                            ->label('Effet de la péripétie (EN)')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('timing')
                            ->label('Timing')
                            ->options([
                                'any_battle_round' => 'N\'importe quel round',
                                'second_battle_round_onwards' => 'À partir du 2e round',
                                'from_battle_round_two' => 'À partir du round 2',
                            ])
                            ->default('any_battle_round')
                            ->required(),
                    ]),

                Forms\Components\Section::make('Métadonnées')
                    ->schema([
                        Forms\Components\TextInput::make('edition')
                            ->label('Édition')
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
                    ->getStateUsing(function (TwistMission $record) {
                        $translation = DB::table('translations')
                            ->where('resource_type', 'TwistMission')
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
                            $join->on('twist_missions.id', '=', 'translations.resource_id')
                                 ->where('translations.resource_type', 'TwistMission')
                                 ->where('translations.field', 'name')
                                 ->where('translations.locale', 'fr');
                        })->orderBy('translations.translated_text', $direction);
                    }),
                Tables\Columns\TextColumn::make('timing')
                    ->label('Timing')
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'chapter-approved-2025-26' => 'Chapter Approved 2025-26',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut'),
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
            RelationManagers\TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTwistMissions::route('/'),
            'create' => Pages\CreateTwistMission::route('/create'),
            'edit' => Pages\EditTwistMission::route('/{record}/edit'),
        ];
    }
}
