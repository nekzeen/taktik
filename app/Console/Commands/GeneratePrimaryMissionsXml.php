<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePrimaryMissionsXml extends Command
{
    protected $signature = 'missions:generate-xml {--output=storage/missions/primary-missions-chapter-approved-2025-26.xml}';
    protected $description = 'Générer le fichier XML des missions primaires en scrapant Wahapedia';

    protected string $url = 'https://wahapedia.ru/wh40k10ed/the-rules/chapter-approved-2025-26/';

    public function handle()
    {
        $this->info('🕷️  Scraping des missions primaires depuis Wahapedia...');
        $this->info('═══════════════════════════════════════════════════════════');

        try {
            $html = @file_get_contents($this->url);
            if ($html === false) {
                $this->error('❌ Impossible de récupérer la page Wahapedia');
                return 1;
            }

            $missions = $this->scrapeMissions($html);

            if (empty($missions)) {
                $this->error('❌ Aucune mission trouvée !');
                return 1;
            }

            $xmlContent = $this->generateXml($missions);
            $outputPath = $this->option('output');

            // Créer le répertoire s'il n'existe pas
            $directory = dirname($outputPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($outputPath, $xmlContent);

            $this->info('');
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info("✅ Fichier généré: {$outputPath}");
            $this->info("📊 Missions trouvées: " . count($missions));
            $this->info('═══════════════════════════════════════════════════════════');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            return 1;
        }
    }

    protected function scrapeMissions(string $html): array
    {
        $missions = [];

        // Chercher la section Primary Mission deck
        if (preg_match('/Primary-Mission-deck(.+?)(?=<h2|$)/is', $html, $matches)) {
            $content = $matches[1];
        } else {
            $content = $html;
        }

        // Diviser par les titres de missions (h2, h3, h4 en majuscules)
        if (preg_match_all('/<h[2-4][^>]*>([A-Z\s]+)<\/h[2-4]>(.+?)(?=<h[2-4]|$)/is', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $mission = $this->parseMissionBlock($match[1], $match[2]);
                if ($mission && !empty($mission['title'])) {
                    $missions[] = $mission;
                }
            }
        }

        return $missions;
    }

    protected function parseMissionBlock(string $title, string $block): ?array
    {
        $title = trim($title);

        // Extraire la flaveur (première ligne de texte)
        $flavour = '';
        if (preg_match('/<p[^>]*>(.+?)<\/p>/is', $block, $matches)) {
            $flavour = trim(strip_tags($matches[1]));
        }

        // Extraire les sections
        $when = $this->extractSection($block, 'WHEN:|WHEN ');
        $action = $this->extractSection($block, 'ACTION|BURN OBJECTIVE|MOVE HAZARD|TERRAFORM');
        $setup = $this->extractSection($block, 'SETUP:|Start of the Battle');
        $scoring = $this->extractScoringItems($block);
        $notes = 'Source: wahapedia Chapter Approved 2025-26 Primary Mission deck.';

        return [
            'title' => $title,
            'flavour' => $flavour,
            'when' => $when,
            'action' => $action,
            'setup' => $setup,
            'scoring' => $scoring,
            'notes' => $notes,
        ];
    }

    protected function extractSection(string $block, string $pattern): string
    {
        if (preg_match("/{$pattern}(.+?)(?=(?:WHEN|ACTION|SETUP|<h|$))/is", $block, $matches)) {
            $text = trim(strip_tags($matches[1]));
            $text = preg_replace('/\s+/', ' ', $text);
            return substr($text, 0, 500);
        }
        return '';
    }

    protected function extractScoringItems(string $block): array
    {
        $items = [];

        // Chercher les items de scoring dans les <li>
        if (preg_match_all('/<li[^>]*>(.+?)<\/li>/is', $block, $matches)) {
            foreach ($matches[1] as $item) {
                $text = trim(strip_tags($item));
                if (!empty($text)) {
                    $items[] = $text;
                }
            }
        }

        // Si pas de <li>, chercher les paragraphes
        if (empty($items) && preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $block, $matches)) {
            foreach ($matches[1] as $item) {
                $text = trim(strip_tags($item));
                if (!empty($text) && strlen($text) > 20) {
                    $items[] = $text;
                }
            }
        }

        return $items;
    }

    protected function generateXml(array $missions): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><missions/>');

        foreach ($missions as $missionData) {
            $mission = $xml->addChild('mission');
            $mission->addChild('title', htmlspecialchars($missionData['title']));
            $mission->addChild('flavour', htmlspecialchars($missionData['flavour']));

            if (!empty($missionData['when'])) {
                $mission->addChild('when', htmlspecialchars($missionData['when']));
            }

            if (!empty($missionData['action'])) {
                $mission->addChild('action', htmlspecialchars($missionData['action']));
            }

            if (!empty($missionData['setup'])) {
                $mission->addChild('setup', htmlspecialchars($missionData['setup']));
            }

            // Ajouter les items de scoring
            if (!empty($missionData['scoring'])) {
                $scoring = $mission->addChild('scoring');
                foreach ($missionData['scoring'] as $item) {
                    $scoring->addChild('item', htmlspecialchars($item));
                }
            }

            $mission->addChild('notes', htmlspecialchars($missionData['notes']));
        }

        // Formater le XML avec indentation
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }
}
