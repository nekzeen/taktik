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
     * Importer/synchroniser une faction spécifique
     */
    public function importFaction(Faction $faction): array
    {
        $results = [
            'units' => 0,
            'detachments' => 0,
            'errors' => [],
        ];

        try {
            // Récupérer la liste des fichiers depuis GitHub API
            $files = $this->getRepositoryFiles();

            // Chercher le fichier correspondant à la faction
            $factionName = $faction->name;
            $found = false;

            foreach ($files as $file) {
                if (str_ends_with($file['name'], '.cat')) {
                    // Vérifier si le nom du fichier correspond à la faction
                    // (en tenant compte du nettoyage des noms)
                    $cleanFileName = preg_replace('/\s+Library\s*$/i', '', $file['name']);
                    $cleanFileName = str_replace('.cat', '', $cleanFileName);
                    
                    if (strcasecmp($cleanFileName, $factionName) === 0) {
                        try {
                            $counts = $this->importCatalogFile($file['download_url']);
                            $results['units'] += $counts['units'] ?? 0;
                            $results['detachments'] += $counts['detachments'] ?? 0;
                            $found = true;
                            \Log::info("Synchronisation de {$faction->name} réussie");
                        } catch (\Exception $e) {
                            $results['errors'][] = "Erreur lors de la synchronisation : " . $e->getMessage();
                            \Log::error("Erreur synchronisation {$faction->name}", ['error' => $e->getMessage()]);
                        }
                    }
                }
            }

            if (!$found) {
                $results['errors'][] = "Fichier BSData non trouvé pour {$faction->name}";
                \Log::warning("Fichier BSData non trouvé pour {$faction->name}");
            }
        } catch (\Exception $e) {
            $results['errors'][] = 'Erreur générale: ' . $e->getMessage();
            \Log::error('Erreur synchronisation faction', ['error' => $e->getMessage()]);
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
        
        // Nettoyer le nom de la faction (supprimer " Library" à la fin)
        $cleanFactionName = preg_replace('/\s+Library\s*$/i', '', $factionName);
        
        // Trouver ou créer la faction
        $faction = Faction::firstOrCreate(
            ['name' => $cleanFactionName],
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
        // Les détachements sont des selectionEntry[@type="upgrade"]
        // Utiliser local-name() pour ignorer les namespaces
        
        // Essayer d'abord avec les rules
        $detachments = $xml->xpath('//*[local-name()="selectionEntry"][@type="upgrade" and *[local-name()="rules"]/*[local-name()="rule"]]');
        
        // Si aucun détachement trouvé avec rules, essayer sans rules
        if (empty($detachments)) {
            $detachments = $xml->xpath('//*[local-name()="selectionEntry"][@type="upgrade"]');
        }
        
        \Log::info("Import détachements pour {$faction->name}: " . count($detachments) . " candidats trouvés");
        
        // Créer un mapping des commentaires pour les détachements (cas Necron)
        $commentMap = $this->extractDetachmentComments($xml);

        foreach ($detachments as $detachment) {
            $bsdataId = (string) $detachment['id'];
            $name = (string) $detachment['name'];

            // Ignorer les entrées vides ou invalides
            if (empty($name) || empty($bsdataId)) {
                continue;
            }

            // Vérifier si ce détachement a un commentaire (cas Necron)
            if (isset($commentMap[$bsdataId])) {
                $name = $commentMap[$bsdataId];
                \Log::debug("Détachement identifié par commentaire: {$name}");
            }

            // Extraire les règles - les vrais détachements ont TOUJOURS des règles
            $rules = $this->extractRules($detachment);
            
            // Accepter UNIQUEMENT si :
            // 1. Le détachement a des règles, OU
            // 2. Le détachement est identifié par commentaire (cas Necron)
            if (empty($rules) && !isset($commentMap[$bsdataId])) {
                \Log::debug("Ignoré (pas de règles et pas de commentaire): {$name}");
                continue;
            }
            
            \Log::debug("Détachement candidat: {$name} (règles: " . (empty($rules) ? "0" : count($rules)) . ")");

            // Nettoyer le nom
            $cleanName = preg_replace('/^\d+\.\s*/', '', $name);
            $cleanName = trim($cleanName);

            \Log::info("Création détachement: {$cleanName} pour {$faction->name}");

            // Vérifier si ce détachement existe déjà pour cette faction (par nom)
            $existing = BsdataDetachment::where('faction_id', $faction->id)
                ->where('name', $cleanName)
                ->first();
            
            if (!$existing) {
                BsdataDetachment::create([
                    'bsdata_id' => $bsdataId,
                    'faction_id' => $faction->id,
                    'name' => $cleanName,
                    'description' => $this->extractDescription($detachment),
                    'rules' => $rules,
                    'stratagems' => $this->extractStratagems($detachment),
                    'raw_data' => json_decode(json_encode($detachment), true),
                    'is_manual' => false,
                    'manually_modified' => false,
                ]);
                
                $detachmentsImported++;
            } elseif ($existing->manually_modified) {
                // Ne pas modifier les entrées modifiées manuellement
                \Log::debug("Détachement modifié manuellement conservé (non modifié): {$cleanName}");
            } elseif ($existing->is_manual) {
                // Ne pas modifier les entrées manuelles
                \Log::debug("Détachement manuel conservé (non modifié): {$cleanName}");
            } else {
                \Log::debug("Détachement déjà existant (doublon): {$cleanName}");
            }
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

    /**
     * Extraire les commentaires de détachements (pour les factions comme Necrons)
     * Les détachements peuvent être identifiés par des commentaires XML
     */
    protected function extractDetachmentComments(\SimpleXMLElement $xml): array
    {
        $commentMap = [];
        
        // Convertir le XML en string pour chercher les commentaires
        $xmlString = $xml->asXML();
        
        // Pattern pour trouver les commentaires suivis de selectionEntry
        // <comment>Nom du Détachement</comment>
        // </selectionEntry>
        // <selectionEntry ... id="xxx" ...>
        $pattern = '/<comment>([^<]+)<\/comment>\s*<\/selectionEntry>\s*<selectionEntry[^>]*id="([^"]*)"[^>]*>/i';
        
        if (preg_match_all($pattern, $xmlString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $commentName = trim($match[1]);
                $entryId = $match[2];
                $commentMap[$entryId] = $commentName;
            }
        }
        
        return $commentMap;
    }
}
