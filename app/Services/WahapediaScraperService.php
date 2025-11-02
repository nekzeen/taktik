<?php

namespace App\Services;

use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class WahapediaScraperService
{
    protected Client $client;
    protected string $baseUrl = 'https://wahapedia.ru/wh40k10ed/the-rules/chapter-approved-2025-26/';

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
        ]);
    }

    /**
     * Scraper les missions primaires depuis Wahapedia
     */
    public function scrapePrimaryMissions(): array
    {
        try {
            $response = $this->client->get($this->baseUrl);
            $html = (string) $response->getBody();

            $crawler = new Crawler($html);
            $missions = [];

            // Chercher les sections de missions primaires
            $crawler->filter('h2, h3, h4')->each(function (Crawler $node) use (&$missions) {
                $text = trim($node->text());

                // Chercher les titres de missions (généralement en majuscules)
                if (preg_match('/^[A-Z\s]+$/', $text) && strlen($text) > 3) {
                    $missions[] = [
                        'name' => $text,
                        'title_element' => $node,
                    ];
                }
            });

            // Extraire les détails de chaque mission
            $extractedMissions = [];
            foreach ($missions as $mission) {
                $details = $this->extractMissionDetails($mission['name'], $crawler);
                if ($details) {
                    $extractedMissions[] = $details;
                }
            }

            return $extractedMissions;
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors du scraping de Wahapedia: {$e->getMessage()}");
        }
    }

    /**
     * Extraire les détails d'une mission
     */
    protected function extractMissionDetails(string $missionName, Crawler $crawler): ?array
    {
        try {
            $description = '';
            $fullText = '';
            $whenCondition = '';
            $scoringConditions = [];

            // Chercher le contenu après le titre de la mission
            $content = $crawler->filter('body')->html();

            // Utiliser regex pour extraire la section de la mission
            $pattern = '/(?:Primary Mission\s*)?(' . preg_quote($missionName) . ')(.+?)(?=Primary Mission|$)/is';

            if (preg_match($pattern, $content, $matches)) {
                $missionContent = $matches[2];

                // Extraire la description (première ligne après le titre)
                if (preg_match('/^(.+?)(?:\n|<br|WHEN:|SECOND)/i', $missionContent, $descMatch)) {
                    $description = trim(strip_tags($descMatch[1]));
                }

                // Extraire la condition WHEN
                if (preg_match('/WHEN:\s*(.+?)(?:\n|<br|If the|OR)/is', $missionContent, $whenMatch)) {
                    $whenCondition = trim(strip_tags($whenMatch[1]));
                }

                // Extraire les conditions de scoring
                if (preg_match_all('/If\s+(.+?)(?:they score|they get|score)\s+(\d+)VP/is', $missionContent, $scoringMatches, PREG_SET_ORDER)) {
                    foreach ($scoringMatches as $match) {
                        $scoringConditions[] = [
                            'condition' => trim(strip_tags($match[1])),
                            'vp' => trim(strip_tags($match[2])) . 'VP',
                        ];
                    }
                }

                // Texte complet
                $fullText = "Primary Mission\n" . $missionName . "\n" . $description . "\n\n" . $missionContent;

                return [
                    'name' => $missionName,
                    'description' => $description,
                    'full_text' => $fullText,
                    'when_condition' => $whenCondition,
                    'timing' => 'second_battle_round_onwards',
                    'scoring_conditions' => $scoringConditions,
                    'max_vp' => 15,
                    'edition' => '10ed',
                    'source' => 'chapter-approved-2025-26',
                    'slug' => Str::slug($missionName),
                ];
            }

            return null;
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de l'extraction de {$missionName}: {$e->getMessage()}");
        }
    }
}
