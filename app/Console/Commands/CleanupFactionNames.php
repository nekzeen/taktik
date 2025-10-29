<?php

namespace App\Console\Commands;

use App\Models\Faction;
use Illuminate\Console\Command;

class CleanupFactionNames extends Command
{
    protected $signature = 'factions:cleanup';
    protected $description = 'Nettoyer les noms des factions (supprimer les tirets inutiles)';

    public function handle(): int
    {
        $this->info('Nettoyage des noms de factions...');

        $factions = Faction::all();
        $count = 0;

        foreach ($factions as $faction) {
            $oldName = $faction->name;
            
            // Nettoyer : trim + supprimer tirets/espaces inutiles au début et fin
            $newName = trim($faction->name);
            $newName = trim($newName, ' -');
            $newName = preg_replace('/\s+/', ' ', $newName); // Supprimer les espaces multiples
            
            if ($oldName !== $newName && !empty($newName)) {
                $this->line("Avant: '{$oldName}'");
                $this->line("Après: '{$newName}'\n");
                $faction->name = $newName;
                $faction->save();
                $count++;
            }
        }

        $this->info("✅ {$count} faction(s) nettoyée(s)");
        return 0;
    }
}
