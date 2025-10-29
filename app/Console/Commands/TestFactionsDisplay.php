<?php

namespace App\Console\Commands;

use App\Models\Faction;
use Illuminate\Console\Command;

class TestFactionsDisplay extends Command
{
    protected $signature = 'factions:test-display';
    protected $description = 'Tester l\'affichage des factions';

    public function handle(): int
    {
        $factions = Faction::whereHas('detachments')
            ->orderBy('name')
            ->get()
            ->filter(function ($faction) {
                // Utiliser name_fr si non-vide, sinon name
                $name = !empty(trim($faction->name_fr)) ? $faction->name_fr : $faction->name;
                return !empty(trim($name));
            })
            ->values();

        $this->info('Factions filtrées: ' . $factions->count() . "\n");

        foreach ($factions as $f) {
            $displayName = !empty(trim($f->name_fr)) ? $f->name_fr : $f->name;
            $this->line("  • '{$displayName}' ({$f->detachments->count()} détachements)");
        }

        return 0;
    }
}
