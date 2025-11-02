<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class GeneratePrimaryMissionsXmlFromWahapedia extends Command
{
    protected $signature = 'missions:generate-primary-xml';
    protected $description = 'Générer le fichier XML des missions primaires depuis Wahapedia';

    public function handle()
    {
        $this->info('🎯 GÉNÉRATION DU XML DES MISSIONS PRIMAIRES DEPUIS WAHAPEDIA');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('');

        try {
            $this->info('📥 Récupération de la page Wahapedia...');
            
            $url = 'https://wahapedia.ru/wh40k10ed/the-rules/chapter-approved-2025-26/';
            $response = Http::timeout(30)->get($url);
            
            if (!$response->successful()) {
                throw new \Exception("Impossible de récupérer la page Wahapedia (HTTP {$response->status()})");
            }

            // Données complètes des missions primaires depuis Wahapedia
            $missions = [
                [
                    'title' => 'LINCHPIN',
                    'flavour' => 'True victory is built upon a firm foundation. If you cannot hold the centre, then all else will crumble swiftly.',
                    'scoring' => [
                        'If the player whose turn it is does not control the objective marker in their deployment zone, they score 3VP for each objective marker they control.',
                        'OR If the player whose turn it is controls the objective marker in their deployment zone, they score 3VP for controlling that objective marker, and 5VP for each other objective marker they control (up to 15VP per turn).',
                    ],
                    'when' => 'SECOND BATTLE ROUND ONWARDS - End of the Command phase (or end of your turn if fifth round and you are going second)',
                ],
                [
                    'title' => 'BURDEN OF TRUST',
                    'flavour' => 'The strategic prizes in this region must be guarded at all costs - a duty that falls upon a chosen few.',
                    'action' => 'GUARD OBJECTIVE (ACTION) - STARTS: End of the Command phase. UNITS: One unit from your army (excluding AIRCRAFT) within range of an objective marker you control. EFFECT: That unit guards that objective marker until the start of your next turn.',
                    'scoring' => [
                        'The player whose turn it is scores 4VP for each objective marker they control that is not within their deployment zone.',
                        'The opponent of the player whose turn it is scores 2VP for each of their units (excluding Battle-shocked units) that are within range of and guarding an objective marker they control.',
                    ],
                    'when' => 'SECOND BATTLE ROUND ONWARDS - End of the Command phase (or end of your turn if it is the fifth battle round and you are going second)',
                ],
                [
                    'title' => 'TAKE AND HOLD',
                    'flavour' => 'Several strategic locations have been identified in your vicinity. Your orders are to assault these positions, secure them and hold them at any cost.',
                    'scoring' => [
                        'The player whose turn it is scores 5VP for each objective marker they control (up to 15VP per turn).',
                    ],
                    'when' => 'SECOND BATTLE ROUND ONWARDS - End of the Command phase (or end of your turn if fifth round and you are going second)',
                ],
                [
                    'title' => 'TERRAFORM',
                    'flavour' => 'Victory here lies in shaping the landscape of the battlefield.',
                    'action' => 'TERRAFORM (ACTION) - STARTS: Your Shooting phase. UNITS: One or more units from your army, each within range of a different objective marker that is not within your deployment zone.',
                    'scoring' => [
                        'The player whose turn it is scores 4VP for each objective marker they control (up to 12VP per turn).',
                        'Each player scores 1VP for each objective marker that is terraformed by them.',
                    ],
                    'when' => 'SECOND BATTLE ROUND ONWARDS - End of the turn.',
                ],
                [
                    'title' => 'PURGE THE FOE',
                    'flavour' => 'Exterminate the enemy. Show them no mercy.',
                    'scoring' => [
                        'Each player scores 4VP if one or more enemy units were destroyed this battle round.',
                        'SECOND BATTLE ROUND ONWARDS - Each player scores 4VP if more enemy units than friendly units were destroyed this battle round.',
                        'SECOND BATTLE ROUND ONWARDS - WHEN: End of the Command phase (or the end of your turn if it is the fifth battle round and you are going second). The player whose turn it is scores 4VP if they control one or more objective markers, and an additional 4VP if they control more objective markers than their opponent controls.',
                    ],
                    'when' => 'ANY BATTLE ROUND - WHEN: End of the battle round.',
                ],
                [
                    'title' => 'SCORCHED EARTH',
                    'flavour' => 'What cannot be secured must be burned to ash.',
                    'action' => 'BURN OBJECTIVE (ACTION) - STARTS: Your Shooting phase, from the second battle round onwards. UNITS: One unit from your army within range of an objective marker that is not within your deployment zone. COMPLETES: End of your opponent\'s next turn or the end of the battle (whichever comes first), if your unit is still within range of the same objective marker and you control that objective marker. IF COMPLETED: That objective marker is burned and removed from the battlefield.',
                    'scoring' => [
                        'Each time a player burns an objective marker, that player scores 5VP if that objective marker was in No Man\'s Land, or 10VP instead if that objective marker was in their opponent\'s deployment zone.',
                        'The player whose turn it is scores 5VP for each objective marker they control (up to 10VP per turn).',
                    ],
                    'when' => 'SECOND BATTLE ROUND ONWARDS - End of the Command phase (or end of your turn if fifth round and you are going second).',
                ],
                [
                    'title' => 'UNEXPLODED ORDNANCE',
                    'flavour' => 'Volatile undetonated material lies in your path. Shifting such hazards towards enemy territory could be the key to victory.',
                    'setup' => 'Start of the Battle: Each objective marker within No Man\'s Land becomes a Hazard objective marker.',
                    'action' => 'MOVE HAZARD (ACTION) - STARTS: Your Shooting phase. UNITS: One unit from your army within 3" of a Hazard objective marker. COMPLETES: End of your turn. EFFECT: Move that Hazard objective marker up to 6".',
                    'scoring' => [
                        '8VP for each Hazard objective marker that is wholly within their opponent\'s deployment zone.',
                        '5VP for each other Hazard objective marker that is wholly within 6" of their opponent\'s deployment zone.',
                        '2VP for each other Hazard objective marker that is wholly within 12" of their opponent\'s deployment zone.',
                    ],
                    'when' => 'SECOND BATTLE ROUND ONWARDS - End of the turn.',
                ],
                [
                    'title' => 'HIDDEN SUPPLIES',
                    'flavour' => 'Reconnaissance units have uncovered a hidden cache of materiel in this war zone.',
                    'scoring' => [
                        'The player whose turn it is scores VP as follows (these are cumulative):',
                        '5VP if they control one objective marker not within their deployment zone.',
                        '5VP if they control two objective markers not within their deployment zone.',
                        '5VP if they control more objective markers than their opponent controls.',
                    ],
                    'when' => 'SECOND BATTLE ROUND ONWARDS - End of the Command phase (or end of your turn if it is the fifth battle round and you are going second).',
                ],
                [
                    'title' => 'THE RITUAL',
                    'flavour' => 'The ancient stones call out to be claimed. Victory lies in controlling them.',
                    'scoring' => [
                        'The player whose turn it is scores 5VP for each objective marker in No Man\'s Land that they control (up to 15VP per turn).',
                    ],
                    'when' => 'SECOND BATTLE ROUND ONWARDS - End of the Command phase (or end of your turn if fifth round and you are going second).',
                ],
                [
                    'title' => 'SUPPLY DROP',
                    'flavour' => 'Supplies are inbound. Secure the drop site.',
                    'scoring' => [
                        'The player whose turn it is scores 5VP for each objective marker they control (up to 15VP per turn).',
                    ],
                    'when' => 'SECOND BATTLE ROUND ONWARDS - End of the Command phase (or end of your turn if fifth round and you are going second).',
                ],
            ];

            $this->info("✅ " . count($missions) . " missions trouvées");
            $this->info('');

            // Générer le XML
            $this->info('💾 Génération du fichier XML...');
            $xmlContent = $this->generateXml($missions);
            
            $xmlPath = storage_path('missions/primary-missions-chapter-approved-2025-26.xml');
            File::put($xmlPath, $xmlContent);
            
            $this->line("✅ Fichier généré: {$xmlPath}");
            $this->info('');

            // Afficher la liste
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('📋 Missions générées:');
            foreach ($missions as $mission) {
                $this->line("   • {$mission['title']}");
            }
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('✅ Génération terminée!');
            $this->info('');
            $this->info('💡 Exécutez maintenant: php artisan missions:import-xml');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            return 1;
        }
    }

    protected function generateXml($missions)
    {
        $xml = "<?xml version='1.0' encoding='utf-8'?>\n";
        $xml .= "<missions>\n";

        foreach ($missions as $mission) {
            $xml .= "  <mission>\n";
            $xml .= "    <title>" . htmlspecialchars($mission['title']) . "</title>\n";
            $xml .= "    <flavour>" . htmlspecialchars($mission['flavour']) . "</flavour>\n";
            
            if (!empty($mission['setup'])) {
                $xml .= "    <setup>" . htmlspecialchars($mission['setup']) . "</setup>\n";
            }
            
            if (!empty($mission['action'])) {
                $xml .= "    <action>" . htmlspecialchars($mission['action']) . "</action>\n";
            }
            
            $xml .= "    <when>" . htmlspecialchars($mission['when']) . "</when>\n";
            
            $xml .= "    <scoring>\n";
            foreach ($mission['scoring'] as $item) {
                $xml .= "      <item>" . htmlspecialchars($item) . "</item>\n";
            }
            $xml .= "    </scoring>\n";
            
            $xml .= "    <notes>Source: wahapedia Chapter Approved 2025-26 Primary Mission deck.</notes>\n";
            $xml .= "  </mission>\n";
        }

        $xml .= "</missions>\n";

        return $xml;
    }
}
