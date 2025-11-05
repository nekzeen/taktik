<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateFactionsTranslations extends Command
{
    protected $signature = 'factions:create-translations';
    protected $description = 'Créer les traductions pour les factions dans la table translations';

    public function handle()
    {
        $this->info('🚀 Création des traductions pour les factions...');

        try {
            $factions = DB::table('factions')->get();
            $created = 0;
            $skipped = 0;

            foreach ($factions as $faction) {
                // Vérifier si la traduction existe déjà
                $exists = DB::table('translations')
                    ->where('resource_type', 'Faction')
                    ->where('resource_id', $faction->id)
                    ->where('field', 'name')
                    ->where('locale', 'fr')
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Créer la traduction
                DB::table('translations')->insert([
                    'resource_type' => 'Faction',
                    'resource_id' => $faction->id,
                    'field' => 'name',
                    'locale' => 'fr',
                    'source_text' => $faction->name,
                    'translated_text' => $faction->name_fr,
                    'status' => 'auto',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $created++;
                $this->line("  ✅ {$faction->name} → {$faction->name_fr}");
            }

            $this->info("\n✅ Traductions créées : $created");
            $this->info("⏭️  Traductions existantes : $skipped");
            $this->info("✅ Terminé !");

        } catch (\Exception $e) {
            $this->error("❌ Erreur : {$e->getMessage()}");
        }
    }
}
