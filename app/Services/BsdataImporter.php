<?php

namespace App\Services;

use App\Models\Faction;
use App\Models\BsdataUnit;
use App\Models\BsdataDetachment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BsdataImporter
{
    protected string $githubRepo = 'BSData/wh40k-10e';
    protected string $branch = 'main';
    protected string $baseUrl = 'https://raw.githubusercontent.com';

    /**
     * Télécharger et importer toutes les données BSData
     */
    public function importAll(): array
    {
        $results = [
            'factions' => 0,
            'units' => 0,
            'detachments' => 0,
            'errors' => [],
        ];

        try {
            // Récupérer la liste des fichiers depuis GitHub API
            $files = $this->getRepositoryFiles();

            foreach ($files as $file) {
                if (str_ends_with($file['name'], '.cat')) {
                    try {
                        $counts = $this->importCatalogFile($file['download_url']);
                        $results['factions']++;
                        $results['units'] += $counts['units'] ?? 0;
                        $results['detachments'] += $counts['detachments'] ?? 0;
                    } catch (\Exception $e) {
                        $results['errors'][] = "Erreur fichier {$file['name']}: " . $e->getMessage();
                    }
                }
            }

            \Log::info('Import BSData terminé', $results);
        } catch (\Exception $e) {
            $results['errors'][] = 'Erreur générale: ' . $e->getMessage();
            \Log::error('Erreur import BSData', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Récupérer la liste des fichiers du repository
     */
    protected function getRepositoryFiles(): array
    {
        $url = "https://api.github.com/repos/{$this->githubRepo}/contents";
        
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'WH40k-Tournament-App',
        ])->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Impossible de récupérer les fichiers du repository');
    }

    /**
     * Importer un fichier catalogue (.cat)
     */
    protected function importCatalogFile(string $url): array
    {
        $response = Http::get($url);
        
        if (!$response->successful()) {
            throw new \Exception('Impossible de télécharger le fichier');
        }

        $xml = simplexml_load_string($response->body());
        
        if ($xml === false) {
            throw new \Exception('Impossible de parser le XML');
        }

        // Extraire le nom de la faction
        $factionName = (string) $xml['name'];
        
        // Trouver ou créer la faction
        $faction = Faction::firstOrCreate(
            ['name' => $factionName],
            ['bsdata_id' => (string) $xml['id']]
        );

        // Compter avant import
        $unitsBefore = BsdataUnit::where('faction_id', $faction->id)->count();
        $detachmentsBefore = BsdataDetachment::where('faction_id', $faction->id)->count();

        // Importer les unités
        $this->importUnits($xml, $faction);

        // Importer les détachements
        $this->importDetachments($xml, $faction);

        // Compter après import
        $unitsAfter = BsdataUnit::where('faction_id', $faction->id)->count();
        $detachmentsAfter = BsdataDetachment::where('faction_id', $faction->id)->count();

        return [
            'units' => $unitsAfter - $unitsBefore,
            'detachments' => $detachmentsAfter - $detachmentsBefore,
        ];
    }

    /**
     * Importer les unités depuis le XML
     */
    protected function importUnits(\SimpleXMLElement $xml, Faction $faction): void
    {
        // Les unités sont dans <selectionEntries>
        foreach ($xml->xpath('//selectionEntry[@type="unit"]') as $unit) {
            $bsdataId = (string) $unit['id'];
            $name = (string) $unit['name'];

            // Extraire les points
            $points = $this->extractPoints($unit);

            // Extraire les mots-clés
            $keywords = $this->extractKeywords($unit);

            // Créer ou mettre à jour l'unité
            BsdataUnit::updateOrCreate(
                ['bsdata_id' => $bsdataId],
                [
                    'faction_id' => $faction->id,
                    'name' => $name,
                    'type' => $this->detectUnitType($unit),
                    'points_min' => $points['min'] ?? null,
                    'points_max' => $points['max'] ?? null,
                    'keywords' => $keywords,
                    'abilities' => $this->extractAbilities($unit),
                    'wargear' => $this->extractWargear($unit),
                    'raw_data' => json_decode(json_encode($unit), true),
                ]
            );
        }
    }

    /**
     * Importer les détachements depuis le XML
     */
    protected function importDetachments(\SimpleXMLElement $xml, Faction $faction): void
    {
        $detachmentsImported = 0;

        // Enregistrer le namespace XML de BattleScribe
        $xml->registerXPathNamespace('cat', 'http://www.battlescribe.net/schema/catalogueSchema');

        // Pattern spécifique basé sur la vraie structure BSData
        // Les détachements sont des selectionEntry[@type="upgrade"] avec des rules
        $detachments = $xml->xpath('//cat:selectionEntry[@type="upgrade" and cat:rules/cat:rule]');
        
        \Log::info("Import détachements pour {$faction->name}: " . count($detachments) . " candidats trouvés");

        foreach ($detachments as $detachment) {
            $bsdataId = (string) $detachment['id'];
            $name = (string) $detachment['name'];

            // Ignorer les entrées vides ou invalides
            if (empty($name) || empty($bsdataId)) {
                continue;
            }

            // Filtrer les armes et équipements (patterns communs)
            $weaponKeywords = [
                'cannon', 'gun', 'weapon', 'blade', 'sword', 'rifle', 'pistol',
                'launcher', 'missile', 'grenade', 'melta', 'plasma', 'bolter',
                'chainsword', 'power fist', 'thunder hammer', 'storm shield',
                'autocannon', 'lascannon', 'heavy bolter', 'flamer',
                'feet', 'armour', 'armor', 'wargear', 'equipment'
            ];
            
            $isWeapon = false;
            $lowerName = strtolower($name);
            foreach ($weaponKeywords as $keyword) {
                if (stripos($lowerName, $keyword) !== false) {
                    $isWeapon = true;
                    break;
                }
            }
            
            if ($isWeapon) {
                \Log::debug("Ignoré (arme): {$name}");
                continue;
            }

            // Filtrer uniquement les vrais détachements (qui ont des règles de détachement)
            $rules = $this->extractRules($detachment);
            if (empty($rules)) {
                \Log::debug("Ignoré (pas de règles): {$name}");
                continue;
            }

            // Nettoyer le nom
            $cleanName = preg_replace('/^\d+\.\s*/', '', $name);
            $cleanName = trim($cleanName);

            \Log::info("Création détachement: {$cleanName} pour {$faction->name}");

            BsdataDetachment::updateOrCreate(
                ['bsdata_id' => $bsdataId],
                [
                    'faction_id' => $faction->id,
                    'name' => $cleanName,
                    'description' => $this->extractDescription($detachment),
                    'rules' => $rules,
                    'stratagems' => $this->extractStratagems($detachment),
                    'raw_data' => json_decode(json_encode($detachment), true),
                ]
            );

            $detachmentsImported++;
        }

        \Log::info("Détachements importés pour {$faction->name}: {$detachmentsImported}");
    }

    /**
     * Extraire les points d'une unité
     */
    protected function extractPoints(\SimpleXMLElement $unit): array
    {
        $points = ['min' => null, 'max' => null];

        foreach ($unit->xpath('.//cost[@name="pts"]') as $cost) {
            $value = (int) $cost['value'];
            if ($points['min'] === null || $value < $points['min']) {
                $points['min'] = $value;
            }
            if ($points['max'] === null || $value > $points['max']) {
                $points['max'] = $value;
            }
        }

        return $points;
    }

    /**
     * Extraire les mots-clés
     */
    protected function extractKeywords(\SimpleXMLElement $unit): array
    {
        $keywords = [];

        foreach ($unit->xpath('.//category') as $category) {
            $keywords[] = (string) $category['name'];
        }

        return array_unique($keywords);
    }

    /**
     * Détecter le type d'unité (HQ, Troops, etc.)
     */
    protected function detectUnitType(\SimpleXMLElement $unit): ?string
    {
        $categories = $this->extractKeywords($unit);

        $types = ['HQ', 'Troops', 'Elites', 'Fast Attack', 'Heavy Support', 'Flyer', 'Dedicated Transport'];

        foreach ($types as $type) {
            if (in_array($type, $categories)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Extraire les capacités
     */
    protected function extractAbilities(\SimpleXMLElement $unit): array
    {
        $abilities = [];

        foreach ($unit->xpath('.//rule') as $rule) {
            $abilities[] = [
                'name' => (string) $rule['name'],
                'description' => (string) $rule->description,
            ];
        }

        return $abilities;
    }

    /**
     * Extraire l'équipement
     */
    protected function extractWargear(\SimpleXMLElement $unit): array
    {
        $wargear = [];

        foreach ($unit->xpath('.//selectionEntry[@type="upgrade"]') as $item) {
            $wargear[] = (string) $item['name'];
        }

        return $wargear;
    }

    /**
     * Extraire la description
     */
    protected function extractDescription(\SimpleXMLElement $element): ?string
    {
        $description = $element->xpath('.//description');
        return $description ? (string) $description[0] : null;
    }

    /**
     * Extraire les règles
     */
    protected function extractRules(\SimpleXMLElement $element): array
    {
        $rules = [];

        // Enregistrer le namespace
        $element->registerXPathNamespace('cat', 'http://www.battlescribe.net/schema/catalogueSchema');

        // Chercher les règles avec le namespace
        foreach ($element->xpath('.//cat:rule') as $rule) {
            $rules[] = [
                'name' => (string) $rule['name'],
                'description' => (string) $rule->description,
            ];
        }

        return $rules;
    }

    /**
     * Extraire les stratagèmes
     */
    protected function extractStratagems(\SimpleXMLElement $element): array
    {
        $stratagems = [];

        // Les stratagèmes peuvent être dans des infoLinks ou des selectionEntries
        foreach ($element->xpath('.//infoLink[@type="rule"]') as $stratagem) {
            $stratagems[] = [
                'name' => (string) $stratagem['name'],
                'id' => (string) $stratagem['targetId'],
            ];
        }

        return $stratagems;
    }
}
