<?php

namespace App\Filament\Forms\Components;

use App\Helpers\DetachmentFormatter;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;

class DetachmentSelect extends Select
{
    public static function make(string $name = 'detachment_id'): static
    {
        return parent::make($name)
            ->label('Détachement')
            ->options(self::getDetachmentOptions())
            ->required()
            ->searchable()
            ->preload();
    }

    /**
     * Obtenir les options formatées pour le select
     */
    private static function getDetachmentOptions(): array
    {
        $detachments = DB::table('detachments')
            ->join('factions', 'detachments.faction_id', '=', 'factions.id')
            ->select('detachments.id', 'detachments.name', 'factions.name as faction_name')
            ->orderBy('factions.name')
            ->orderBy('detachments.name')
            ->get();

        $options = [];

        foreach ($detachments as $detachment) {
            $formatted = DetachmentFormatter::format($detachment->id);
            $options[$detachment->id] = "{$detachment->faction_name} - {$formatted}";
        }

        return $options;
    }
}
