<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DetachmentResource\Pages;
use App\Filament\Resources\DetachmentResource\RelationManagers;
use App\Models\Detachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DetachmentResource extends Resource
{
    protected static ?string $model = Detachment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Détachements Wahapedia';
    protected static ?string $navigationGroup = 'Wahapedia';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('wahapedia_id')
                            ->label('Wahapedia ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Select::make('faction_id')
                            ->label('Faction')
                            ->relationship('faction', 'name')
                            ->required()
                            ->searchable(),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TranslationsRelationManager::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('translation_fr')
                    ->label('Traduction (FR)')
                    ->getStateUsing(function (Detachment $record) {
                        $translation = DB::table('translations')
                            ->where('resource_type', 'Detachment')
                            ->where('resource_id', $record->id)
                            ->where('field', 'name')
                            ->where('locale', 'fr')
                            ->first();
                        
                        if ($translation) {
                            $status = match($translation->status) {
                                'pending' => '⏳',
                                'auto' => '🤖',
                                'reviewed' => '👁️',
                                'approved' => '✅',
                                default => '❓',
                            };
                            return $status . ' ' . $translation->translated_text;
                        }
                        return '—';
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
                            $join->on('detachments.id', '=', 'translations.resource_id')
                                 ->where('translations.resource_type', 'Detachment')
                                 ->where('translations.field', 'name')
                                 ->where('translations.locale', 'fr');
                        })->orderBy('translations.translated_text', $direction);
                    }),

                Tables\Columns\TextColumn::make('faction.name')
                    ->label('Faction')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('wahapedia_id')
                    ->label('Wahapedia ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('abilities_count')
                    ->label('Capacités')
                    ->counts('abilities')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('faction')
                    ->relationship('faction', 'name'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetachments::route('/'),
            'create' => Pages\CreateDetachment::route('/create'),
            'edit' => Pages\EditDetachment::route('/{record}/edit'),
        ];
    }
}
