<?php

namespace App\Services;

use App\Models\Faction;
use App\Models\BsdataUnit;
use App\Models\BsdataDetachment;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArmyListAnalyzer
{
    protected Parser $pdfParser;

    public function __construct()
    {
        $this->pdfParser = new Parser();
    }

    /**
     * Alias pour analyze() - pour la compatibilité
     */
    public function analyzePdf(string $pdfPath): array
    {
        return $this->analyze($pdfPath);
    }

    /**
     * Analyser un PDF de liste d'armée et extraire les informations
     * 
     * @param string $pdfPath Chemin vers le fichier PDF (peut être un chemin absolu ou relatif au storage)
     */
    public function analyze(string $pdfPath): array
    {
        try {
            // Déterminer si c'est un chemin absolu ou relatif
            $content = null;
            
            if (file_exists($pdfPath)) {
                // Chemin absolu - lire directement le fichier
                $content = file_get_contents($pdfPath);
            } elseif (Storage::disk('private')->exists($pdfPath)) {
                // Chemin relatif - utiliser le disk private
                $content = Storage::disk('private')->get($pdfPath);
            } elseif (Storage::disk('public')->exists($pdfPath)) {
                // Chemin relatif - utiliser le disk public
                $content = Storage::disk('public')->get($pdfPath);
            } else {
                throw new \Exception('Le fichier PDF n\'existe pas : ' . $pdfPath);
            }
            
            if (empty($content)) {
                throw new \Exception('Le fichier PDF est vide');
            }
            
            // Parser le PDF
            $pdf = $this->pdfParser->parseContent($content);
            $text = $pdf->getText();

            // Extraire les informations
            $faction = $this->detectFaction($text);
            $detachment = $this->detectDetachment($text);
            $points = $this->detectPoints($text);
            $units = $this->extractUnits($text);

            return [
                'faction_id' => $faction?->id,
                'faction_name' => $faction?->name,
                'detachment' => $detachment,
                'points' => $points,
                'units' => $units,
                'raw_text' => $text,
            ];
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'analyse du PDF: ' . $e->getMessage());
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Détecter la faction depuis le texte
     */
    public function detectFaction(string $text): ?Faction
    {
        // Normaliser le texte pour la recherche
        $normalizedText = mb_strtolower($text, 'UTF-8');

        // Recherche de patterns communs avec création automatique
        $patterns = [
            'Space Marines' => ['space marine', 'adeptus astartes', 'ultramarines', 'blood angels', 'dark angels', 'space wolves'],
            'Necrons' => ['necron', 'necrontyr'],
            'Orks' => ['ork', 'greenskin', 'waaagh'],
            'Tyranids' => ['tyranid', 'hive fleet', 'nid'],
            'Chaos Space Marines' => ['chaos space marine', 'heretic astartes', 'traitor'],
            'Chaos Knights' => ['chaos knight', 'chaos knights', 'iconoclast', 'infernal'],
            'Imperial Knights' => ['imperial knight', 'questoris', 'armiger'],
            'Astra Militarum' => ['astra militarum', 'imperial guard', 'cadian'],
            'T\'au Empire' => ['tau empire', 't\'au', 'fire warrior', 'kroot'],
            'Aeldari' => ['aeldari', 'craftworld', 'eldar', 'aspect warrior'],
            'Drukhari' => ['drukhari', 'dark eldar', 'kabal'],
            'Adeptus Mechanicus' => ['adeptus mechanicus', 'mechanicus', 'skitarii', 'tech-priest'],
            'Adeptus Custodes' => ['adeptus custodes', 'custodes', 'golden'],
            'Grey Knights' => ['grey knight', 'daemon hunter'],
            'Death Guard' => ['death guard', 'plague marine', 'nurgle'],
            'Thousand Sons' => ['thousand sons', 'rubric marine', 'tzeentch'],
            'World Eaters' => ['world eaters', 'khorne berzerker'],
            'Genestealer Cults' => ['genestealer cult', 'brood brother'],
            'Adepta Sororitas' => ['adepta sororitas', 'sisters of battle', 'battle sister'],
            'Leagues of Votann' => ['leagues of votann', 'votann', 'kin'],
        ];

        // D'abord, rechercher dans les factions existantes en base (priorité)
        $factions = Faction::all();
        foreach ($factions as $faction) {
            // Nettoyer le nom de la faction (enlever les préfixes comme "Chaos - ")
            $cleanFactionName = preg_replace('/^[^-]+-\s*/', '', $faction->name);
            
            if (stripos($normalizedText, strtolower($faction->name)) !== false ||
                stripos($normalizedText, strtolower($cleanFactionName)) !== false) {
                return $faction;
            }
        }

        // Si pas trouvé, utiliser les patterns
        foreach ($patterns as $factionName => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($normalizedText, $keyword) !== false) {
                    // Chercher la faction en base avec le nom du pattern
                    $faction = Faction::where('name', 'like', '%' . $factionName . '%')->first();
                    
                    // Si elle n'existe pas, la créer
                    if (!$faction) {
                        $faction = Faction::create([
                            'name' => $factionName,
                            'bsdata_id' => 'auto-' . Str::slug($factionName),
                        ]);
                        \Log::info("Faction créée automatiquement : {$factionName}");
                    }
                    
                    return $faction;
                }
            }
        }

        return null;
    }

    /**
     * Dictionnaire de traductions FR->EN pour les détachements
     * Format: 'Nom EN (BSData)' => ['variations FR', 'variations EN']
     */
    protected function getDetachmentTranslations(): array
    {
        return [
            'War Horde' => ['horde de guerre', 'horde guerre', 'war horde'],
            'Speedwaaagh' => ['speedwaaagh', 'speed waaagh'],
            'Gitz Gitz' => ['gitz gitz'],
            'Lords of Dread' => ['seigneurs de l\'effroi', 'seigneur de l\'effroi', 'seigneurs effroi', 'lords of dread'],
            'Dread Household' => ['maisonnée de l\'effroi', 'maisonnee de l\'effroi', 'dread household'],
            'Traitoris Lance' => ['lance traitoris', 'traitoris lance'],
            'Noble Lance' => ['lance noble', 'noble lance'],
            'Questor Imperialis' => ['questor imperialis'],
            'Awakened Dynasty' => ['dynastie éveillée', 'dynastie eveillee', 'awakened dynasty'],
            'Canoptek Court' => ['cour canoptek', 'canoptek court'],
            'Invasion Fleet' => ['flotte d\'invasion', 'flotte invasion', 'invasion fleet'],
            'Crusher Stampede' => ['stampede écraseur', 'stampede ecraseur', 'crusher stampede'],
            'Mont\'ka' => ['mont\'ka', 'montka', 'mont ka', 'mont-ka', 'mont ka'],
            'Kauyon' => ['kauyon'],
            'Retaliation Cadre' => ['cadre de représailles', 'cadre de represailles', 'retaliation cadre'],
        ];
    }

    /**
     * Calculer la similarité entre deux chaînes (0-100%)
     */
    protected function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = mb_strtolower(trim($str1), 'UTF-8');
        $str2 = mb_strtolower(trim($str2), 'UTF-8');
        
        // Normaliser les caractères spéciaux
        $str1 = str_replace(["'", "-", " "], "", $str1);
        $str2 = str_replace(["'", "-", " "], "", $str2);
        
        if ($str1 === $str2) {
            return 100.0;
        }
        
        $distance = levenshtein($str1, $str2);
        $maxLen = max(strlen($str1), strlen($str2));
        
        if ($maxLen === 0) {
            return 100.0;
        }
        
        return round((1 - ($distance / $maxLen)) * 100, 2);
    }

    /**
     * Matcher un texte de détachement avec BSData
     * Retourne le nom du détachement BSData le plus probable
     */
    protected function matchDetachmentWithBSData(string $detachmentText, ?int $factionId = null): ?string
    {
        if (empty($detachmentText)) {
            return null;
        }
        
        $normalizedInput = mb_strtolower(trim($detachmentText), 'UTF-8');
        
        // 1. Chercher dans BSData avec correspondance exacte
        $query = BsdataDetachment::query();
        if ($factionId) {
            $query->where('faction_id', $factionId);
        }
        
        $bsdataDetachments = $query->get();
        
        // Match exact (case-insensitive)
        foreach ($bsdataDetachments as $det) {
            if (mb_strtolower($det->name, 'UTF-8') === $normalizedInput) {
                \Log::info("Détachement: Match exact BSData: {$det->name}");
                return $det->name;
            }
        }
        
        // 2. Chercher avec traductions
        $translations = $this->getDetachmentTranslations();
        
        foreach ($translations as $bsdataName => $variations) {
            foreach ($variations as $variation) {
                $similarity = $this->calculateSimilarity($normalizedInput, $variation);
                
                // Seuil de similarité: 85% pour les traductions, 90% pour les variations
                $threshold = (strlen($variation) > 10) ? 85 : 90;
                
                if ($similarity >= $threshold) {
                    \Log::info("Détachement: Match par traduction/variation: {$bsdataName} (similarité: {$similarity}%)");
                    return $bsdataName;
                }
            }
        }
        
        // 3. Chercher dans BSData avec similarité
        $bestMatch = null;
        $bestSimilarity = 0;
        
        foreach ($bsdataDetachments as $det) {
            $similarity = $this->calculateSimilarity($normalizedInput, $det->name);
            
            if ($similarity > $bestSimilarity && $similarity >= 80) {
                $bestSimilarity = $similarity;
                $bestMatch = $det->name;
            }
        }
        
        if ($bestMatch) {
            \Log::info("Détachement: Match par similarité BSData: {$bestMatch} (similarité: {$bestSimilarity}%)");
            return $bestMatch;
        }
        
        \Log::info("Détachement: Aucun match trouvé pour: {$detachmentText}");
        return null;
    }

    /**
     * Détecter le détachement depuis le texte
     */
    public function detectDetachment(string $text): ?string
    {
        \Log::info('=== DÉTECTION DÉTACHEMENT ===');
        \Log::info('Texte original (100 premiers caractères): ' . substr($text, 0, 100));
        
        $normalizedText = mb_strtolower($text, 'UTF-8');
        
        // 1. Essayer d'extraire le détachement avec les patterns
        $patterns = [
            '/detachment\s+[a-z]+\s*[-:]\s*([^\n]+)/i',
            '/détachement\s+[a-z]+\s*[-:]\s*([^\n]+)/i',
            '/detachment\s*[-:]\s*([^\n]+)/i',
            '/détachement\s*[-:]\s*([^\n]+)/i',
            '/army rule\s*[-:]\s*([^\n]+)/i',
            '/règle d\'armée\s*[-:]\s*([^\n]+)/iu',
            '/detachment rule\s*[-:]\s*([^\n]+)/i',
        ];

        $extractedDetachment = null;
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $normalizedText, $matches)) {
                $extractedDetachment = trim($matches[1]);
                // Nettoyer le détachement (enlever les préfixes comme "orks - ")
                $extractedDetachment = preg_replace('/^[a-z\s]+-\s*/iu', '', $extractedDetachment);
                $extractedDetachment = trim($extractedDetachment);
                \Log::info("Détachement extrait du pattern: {$extractedDetachment} (pattern: {$pattern})");
                break;
            }
        }
        
        // 2. Chercher la faction pour affiner la recherche
        $faction = $this->detectFaction($text);
        $factionId = $faction?->id;
        
        // 3. Si un détachement a été extrait, essayer de le matcher avec BSData
        if ($extractedDetachment) {
            $matched = $this->matchDetachmentWithBSData($extractedDetachment, $factionId);
            if ($matched) {
                \Log::info("Détachement extrait matché avec BSData: {$matched}");
                return $matched;
            }
        }
        
        // 4. Chercher dans BSData avec word boundaries
            \Log::info('Recherche dans BSData avec word boundaries...');
        $bsdataDetachments = BsdataDetachment::when($factionId, function($q) use ($factionId) {
            return $q->where('faction_id', $factionId);
        })->get();
        
        $sortedDetachments = $bsdataDetachments->sortByDesc(function($d) {
            return strlen($d->name);
        });
        
        foreach ($sortedDetachments as $detachment) {
            $detachmentNameLower = mb_strtolower($detachment->name, 'UTF-8');
            
            // Match exact avec word boundaries
            $pattern = '/\b' . preg_quote($detachmentNameLower, '/') . '\b/i';
            if (preg_match($pattern, $normalizedText)) {
                \Log::info("Détachement trouvé dans BSData (word boundaries): {$detachment->name}");
                return $detachment->name;
            }
            
            // Match avec normalisation (sans apostrophes/tirets)
            $cleanDetachmentName = str_replace(["'", "-"], "", $detachmentNameLower);
            $cleanText = str_replace(["'", "-"], "", $normalizedText);
            $patternClean = '/\b' . preg_quote($cleanDetachmentName, '/') . '\b/i';
            if (preg_match($patternClean, $cleanText)) {
                \Log::info("Détachement trouvé dans BSData (normalisé): {$detachment->name}");
                return $detachment->name;
            }
        }
        
        // 5. Chercher avec la nouvelle logique de traductions et similarité
        if ($extractedDetachment) {
            $matched = $this->matchDetachmentWithBSData($extractedDetachment, $factionId);
            if ($matched) {
                \Log::info("Détachement matché par traduction/similarité: {$matched}");
                return $matched;
            }
        }
        
        // 6. Chercher dans le texte complet avec la logique de traductions
        $translations = $this->getDetachmentTranslations();
        foreach ($translations as $bsdataName => $variations) {
            foreach ($variations as $variation) {
                $similarity = $this->calculateSimilarity($normalizedText, $variation);
                $threshold = (strlen($variation) > 10) ? 85 : 90;
                
                if ($similarity >= $threshold && stripos($normalizedText, $variation) !== false) {
                    \Log::info("Détachement trouvé par traduction: {$bsdataName} (similarité: {$similarity}%)");
                    return $bsdataName;
                }
            }
        }
        
        \Log::info('Aucun détachement détecté - retour null');
        return null;
    }

    /**
     * Détecter les points depuis le texte
     */
    public function detectPoints(string $text): ?int
    {
        // Rechercher "Total: XXX pts" ou similaire
        $patterns = [
            '/Total[:\s]+(\d+)\s*pts?/i',
            '/(\d+)\s*points?/i',
            '/Army Total[:\s]+(\d+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return (int) $matches[1];
            }
        }

        return null;
    }

    /**
     * Extraire les unités depuis le texte
     */
    protected function extractUnits(string $text): array
    {
        $units = [];

        // Pattern pour détecter les unités (format commun dans les listes)
        // Exemple: "10x Intercessor Squad [150pts]"
        $pattern = '/(\d+)x?\s+([A-Z][a-zA-Z\s]+?)(?:\[(\d+)pts?\])?(?:\n|$)/';
        
        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $units[] = [
                    'quantity' => (int) $match[1],
                    'name' => trim($match[2]),
                    'points' => isset($match[3]) ? (int) $match[3] : null,
                ];
            }
        }

        return $units;
    }

    /**
     * Générer un résumé de la liste d'armée
     */
    public function generateSummary(array $analysis): string
    {
        $summary = [];

        if (isset($analysis['faction_name'])) {
            $summary[] = "**Faction:** " . $analysis['faction_name'];
        }

        if (isset($analysis['detachment'])) {
            $summary[] = "**Détachement:** " . $analysis['detachment'];
        }

        if (isset($analysis['points'])) {
            $summary[] = "**Points:** " . $analysis['points'] . " pts";
        }

        if (isset($analysis['units']) && count($analysis['units']) > 0) {
            $summary[] = "**Unités:** " . count($analysis['units']) . " unités détectées";
        }

        return implode("\n", $summary);
    }

    /**
     * Valider les unités avec BSData
     */
    public function validateUnits(array $units, ?int $factionId = null): array
    {
        $validation = [
            'valid' => [],
            'invalid' => [],
            'warnings' => [],
        ];

        foreach ($units as $unit) {
            $unitName = $unit['name'];
            
            // Rechercher l'unité dans BSData
            $query = BsdataUnit::where('name', 'like', '%' . $unitName . '%');
            
            if ($factionId) {
                $query->where('faction_id', $factionId);
            }
            
            $bsdataUnit = $query->first();

            if ($bsdataUnit) {
                // Vérifier les points si disponibles
                if (isset($unit['points']) && $bsdataUnit->points_min && $bsdataUnit->points_max) {
                    if ($unit['points'] < $bsdataUnit->points_min || $unit['points'] > $bsdataUnit->points_max) {
                        $validation['warnings'][] = "{$unitName}: Points incorrects ({$unit['points']} pts, attendu entre {$bsdataUnit->points_min} et {$bsdataUnit->points_max} pts)";
                    }
                }
                
                $validation['valid'][] = $unitName;
            } else {
                $validation['invalid'][] = $unitName . ' (non trouvée dans BSData)';
            }
        }

        return $validation;
    }

    /**
     * Obtenir les suggestions d'unités pour une faction
     */
    public function getUnitSuggestions(?int $factionId = null, string $search = ''): array
    {
        $query = BsdataUnit::query();

        if ($factionId) {
            $query->where('faction_id', $factionId);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->limit(10)->get()->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'type' => $unit->type,
                'points' => $unit->points_min . '-' . $unit->points_max . ' pts',
            ];
        })->toArray();
    }
}
