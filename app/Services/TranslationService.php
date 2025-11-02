<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;

class TranslationService
{
    /**
     * Traduit un texte
     */
    public function translate(string $text, string $sourceLanguage, string $targetLanguage, string $translator = 'deepl'): string
    {
        if ($sourceLanguage === $targetLanguage) {
            return $text;
        }

        // Vérifier le cache
        $cacheKey = "translation:{$translator}:{$sourceLanguage}:{$targetLanguage}:" . md5($text);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Appeler le traducteur
            $translated = match($translator) {
                'deepl' => $this->translateWithDeepL($text, $sourceLanguage, $targetLanguage),
                'google' => $this->translateWithGoogle($text, $sourceLanguage, $targetLanguage),
                default => throw new Exception("Traducteur inconnu : $translator"),
            };

            // Sauvegarder en base de données
            $this->saveToDatabase($text, $translated, $sourceLanguage, $targetLanguage, $translator);

            // Mettre en cache
            Cache::put($cacheKey, $translated, now()->addDays(30));

            return $translated;
        } catch (Exception $e) {
            // En cas d'erreur, retourner le texte original
            \Log::error("Erreur de traduction : {$e->getMessage()}", [
                'text' => $text,
                'translator' => $translator,
            ]);
            return $text;
        }
    }

    /**
     * Traduit avec DeepL
     */
    private function translateWithDeepL(string $text, string $sourceLanguage, string $targetLanguage): string
    {
        $apiKey = config('translation.services.deepl.key');
        if (!$apiKey) {
            throw new Exception('Clé API DeepL manquante');
        }

        // Déterminer l'endpoint selon le type de clé
        $isFreeTier = strpos($apiKey, ':fx') !== false;
        $endpoint = $isFreeTier ? 'https://api-free.deepl.com/v2/translate' : 'https://api.deepl.com/v2/translate';

        // Mapper les codes de langue DeepL
        $targetLang = $this->mapLanguageToDeepL($targetLanguage);
        $sourceLang = $sourceLanguage === 'en' ? 'EN' : strtoupper($sourceLanguage);

        $response = Http::timeout(30)->asForm()->post($endpoint, [
            'auth_key' => $apiKey,
            'text' => $text,
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
        ]);

        if (!$response->successful()) {
            throw new Exception("Erreur DeepL : {$response->status()} - {$response->body()}");
        }

        $data = $response->json();
        return $data['translations'][0]['text'] ?? $text;
    }

    /**
     * Traduit avec Google Translate
     */
    private function translateWithGoogle(string $text, string $sourceLanguage, string $targetLanguage): string
    {
        $apiKey = config('translation.services.google.key');
        if (!$apiKey) {
            throw new Exception('Clé API Google Translate manquante');
        }

        $response = Http::timeout(30)->post('https://translation.googleapis.com/language/translate/v2', [
            'key' => $apiKey,
            'q' => $text,
            'source_language' => $sourceLanguage,
            'target_language' => $targetLanguage,
        ]);

        if (!$response->successful()) {
            throw new Exception("Erreur Google Translate : {$response->status()} - {$response->body()}");
        }

        $data = $response->json();
        return $data['data']['translations'][0]['translatedText'] ?? $text;
    }

    /**
     * Mapper les codes de langue pour DeepL
     */
    private function mapLanguageToDeepL(string $language): string
    {
        return match($language) {
            'en' => 'EN-US',
            'fr' => 'FR',
            'de' => 'DE',
            'es' => 'ES',
            'it' => 'IT',
            'pt' => 'PT-BR',
            'ru' => 'RU',
            'ja' => 'JA',
            'zh' => 'ZH',
            default => strtoupper($language),
        };
    }

    /**
     * Sauvegarder la traduction en base de données
     */
    private function saveToDatabase(string $sourceText, string $translatedText, string $sourceLanguage, string $targetLanguage, string $translator): void
    {
        try {
            DB::table('translations')->insertOrIgnore([
                'source_text' => $sourceText,
                'translated_text' => $translatedText,
                'source_language' => $sourceLanguage,
                'target_language' => $targetLanguage,
                'translator' => $translator,
                'reviewed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Exception $e) {
            \Log::warning("Impossible de sauvegarder la traduction : {$e->getMessage()}");
        }
    }
}
