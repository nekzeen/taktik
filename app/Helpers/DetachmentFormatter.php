<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class DetachmentFormatter
{
    /**
     * Formater un détachement avec sa traduction
     * Format: "Nom Anglais (Nom Français)"
     */
    public static function format(int $detachmentId): string
    {
        $detachment = DB::table('detachments')->find($detachmentId);
        
        if (!$detachment) {
            return '—';
        }

        $translation = DB::table('translations')
            ->where('resource_type', 'Detachment')
            ->where('resource_id', $detachmentId)
            ->where('field', 'name')
            ->where('locale', 'fr')
            ->first();

        if ($translation && $translation->translated_text && $translation->translated_text !== $detachment->name) {
            return "{$detachment->name} ({$translation->translated_text})";
        }

        return $detachment->name;
    }

    /**
     * Formater un détachement avec objet complet
     */
    public static function formatFromObject($detachment): string
    {
        if (!$detachment || !isset($detachment->id)) {
            return '—';
        }

        return self::format($detachment->id);
    }

    /**
     * Obtenir la liste des détachements formatés pour un select
     */
    public static function getFormattedList(): array
    {
        $detachments = DB::table('detachments')->get();
        $formatted = [];

        foreach ($detachments as $detachment) {
            $formatted[$detachment->id] = self::format($detachment->id);
        }

        return $formatted;
    }

    /**
     * Obtenir la liste des détachements formatés par faction
     */
    public static function getFormattedListByFaction(): array
    {
        $detachments = DB::table('detachments')
            ->join('factions', 'detachments.faction_id', '=', 'factions.id')
            ->select('detachments.*', 'factions.name as faction_name')
            ->orderBy('factions.name')
            ->orderBy('detachments.name')
            ->get();

        $formatted = [];

        foreach ($detachments as $detachment) {
            $factionName = $detachment->faction_name;
            if (!isset($formatted[$factionName])) {
                $formatted[$factionName] = [];
            }
            $formatted[$factionName][$detachment->id] = self::format($detachment->id);
        }

        return $formatted;
    }
}
