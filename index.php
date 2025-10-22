<?php

/**
 * Redirection vers public/index.php
 * Ce fichier permet de servir Laravel depuis la racine
 * sans modifier la configuration du serveur web
 */

// Définir le chemin vers le dossier public
$publicPath = __DIR__ . '/public';

// Vérifier si c'est une requête pour un asset statique
$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '/';

// Extensions d'assets statiques
$staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot', 'map', 'webp'];
$extension = pathinfo($path, PATHINFO_EXTENSION);

// Si c'est un asset statique, le servir depuis public/
if (in_array(strtolower($extension), $staticExtensions)) {
    $filePath = $publicPath . $path;
    if (file_exists($filePath) && is_file($filePath)) {
        // Déterminer le type MIME
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'map' => 'application/json',
            'webp' => 'image/webp',
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        readfile($filePath);
        exit;
    }
}

// Sinon, charger Laravel
require_once $publicPath . '/index.php';
