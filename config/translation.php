<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des services de traduction
    |--------------------------------------------------------------------------
    |
    | Configuration pour les différents services de traduction automatique
    |
    */

    'default' => env('TRANSLATION_SERVICE', 'deepl'),

    'services' => [
        'deepl' => [
            'key' => env('DEEPL_API_KEY', ''),
            'base_url' => 'https://api-free.deepl.com/v1',
            'timeout' => 30,
        ],

        'google' => [
            'key' => env('GOOGLE_TRANSLATE_API_KEY', ''),
            'base_url' => 'https://translation.googleapis.com/language/translate/v2',
            'timeout' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Langues supportées
    |--------------------------------------------------------------------------
    */

    'languages' => [
        'en' => 'Anglais',
        'fr' => 'Français',
        'de' => 'Allemand',
        'es' => 'Espagnol',
        'it' => 'Italien',
        'pt' => 'Portugais',
        'ru' => 'Russe',
        'ja' => 'Japonais',
        'zh' => 'Chinois',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => 30 * 24 * 60 * 60, // 30 jours
];
