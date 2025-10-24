<?php
/**
 * Script de vérification automatisée des fichiers Blade
 * Utilisation: php verify_blade.php resources/views/tournaments/availability-calendar.blade.php
 */

if ($argc < 2) {
    echo "Usage: php verify_blade.php <chemin_fichier_blade>\n";
    exit(1);
}

$filePath = $argv[1];

if (!file_exists($filePath)) {
    echo "❌ Erreur: Fichier non trouvé: $filePath\n";
    exit(1);
}

$content = file_get_contents($filePath);
$lines = explode("\n", $content);

$errors = [];
$warnings = [];

echo "🔍 Vérification du fichier: $filePath\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Vérifier les balises Blade
echo "1️⃣  Vérification de la syntaxe Blade...\n";

// Compter les @if/@endif
$ifCount = substr_count($content, '@if');
$endifCount = substr_count($content, '@endif');
if ($ifCount !== $endifCount) {
    $errors[] = "❌ Mismatch @if/@endif: $ifCount @if vs $endifCount @endif";
}

// Compter les @foreach/@endforeach
$foreachCount = substr_count($content, '@foreach');
$endforeachCount = substr_count($content, '@endforeach');
if ($foreachCount !== $endforeachCount) {
    $errors[] = "❌ Mismatch @foreach/@endforeach: $foreachCount @foreach vs $endforeachCount @endforeach";
}

// Compter les @auth/@endauth
$authCount = substr_count($content, '@auth');
$endauthCount = substr_count($content, '@endauth');
if ($authCount !== $endauthCount) {
    $errors[] = "❌ Mismatch @auth/@endauth: $authCount @auth vs $endauthCount @endauth";
}

// Compter les @section/@endsection
$sectionCount = substr_count($content, '@section');
$endsectionCount = substr_count($content, '@endsection');
if ($sectionCount !== $endsectionCount) {
    $errors[] = "❌ Mismatch @section/@endsection: $sectionCount @section vs $endsectionCount @endsection";
}

// Vérifier @extends ou <x-layout>
if (!preg_match('/@extends\(|<x-\w+-layout>/', $content)) {
    $warnings[] = "⚠️  Pas de @extends() ou <x-layout> trouvé";
}

// 2. Vérifier les balises HTML
echo "2️⃣  Vérification des balises HTML...\n";

$openTags = preg_match_all('/<(\w+)[^>]*(?<!\/)\s*>/', $content, $matches);
$closeTags = preg_match_all('/<\/(\w+)>/', $content, $closeMatches);

// Tags auto-fermants à ignorer
$selfClosing = ['img', 'br', 'hr', 'input', 'meta', 'link', 'svg', 'path'];

// 3. Vérifier Auth::user()
echo "3️⃣  Vérification de l'authentification...\n";

preg_match_all('/Auth::user\(\)->(\w+)/', $content, $authMatches);
if (!empty($authMatches[0])) {
    // Vérifier si chaque Auth::user() est dans un @auth
    foreach ($authMatches[0] as $match) {
        $pos = strpos($content, $match);
        $beforeContent = substr($content, max(0, $pos - 500), 500);
        $authCount = substr_count($beforeContent, '@auth');
        $endauthCount = substr_count($beforeContent, '@endauth');
        
        if ($authCount <= $endauthCount) {
            $warnings[] = "⚠️  Auth::user() trouvé potentiellement en dehors d'un @auth: $match";
        }
    }
}

// 4. Vérifier les styles inline
echo "4️⃣  Vérification des styles inline...\n";

preg_match_all('/style\s*=\s*"[^"]*"/', $content, $styleMatches);
if (!empty($styleMatches[0])) {
    // Ignorer les gradients Tailwind
    $inlineStyles = array_filter($styleMatches[0], function($style) {
        return !preg_match('/background.*gradient|padding|margin|display|flex|border|color|font|width|height/', $style);
    });
    
    if (!empty($inlineStyles)) {
        $warnings[] = "⚠️  Styles inline détectés (préférer Tailwind): " . count($inlineStyles) . " occurrence(s)";
    }
}

// 5. Vérifier les couleurs
echo "5️⃣  Vérification de la cohérence des couleurs...\n";

$redClasses = preg_match_all('/red-(600|700|800)/', $content);
$greenClasses = preg_match_all('/green-(600|700|800)/', $content);
$blueClasses = preg_match_all('/blue-/', $content);

if ($blueClasses > 0) {
    $warnings[] = "⚠️  Couleur bleu détectée (préférer rouge ou vert)";
}

// 6. Vérifier les routes
echo "6️⃣  Vérification des routes...\n";

preg_match_all('/route\([\'"]([^\'"]+)[\'"]/', $content, $routeMatches);
$routes = array_unique($routeMatches[1]);

// Charger les routes disponibles
$routesFile = 'routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    foreach ($routes as $route) {
        if (!preg_match("/'$route'|\"$route\"|->name\('$route'|->name\(\"$route\"", $routesContent)) {
            $warnings[] = "⚠️  Route potentiellement inexistante: $route";
        }
    }
}

// 7. Vérifier les variables
echo "7️⃣  Vérification des variables...\n";

preg_match_all('/\$(\w+)/', $content, $varMatches);
$variables = array_unique($varMatches[1]);

// Variables attendues dans les layouts
$expectedVars = ['tournament', 'allAvailabilities', 'singleAvailabilities', 'periodAvailabilities', 'availability', 'user'];

// 8. Résumé
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RÉSUMÉ DE LA VÉRIFICATION\n";
echo str_repeat("=", 60) . "\n\n";

if (empty($errors)) {
    echo "✅ AUCUNE ERREUR CRITIQUE DÉTECTÉE\n\n";
} else {
    echo "❌ ERREURS TROUVÉES:\n";
    foreach ($errors as $error) {
        echo "  $error\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  AVERTISSEMENTS:\n";
    foreach ($warnings as $warning) {
        echo "  $warning\n";
    }
    echo "\n";
} else {
    echo "✅ Aucun avertissement\n\n";
}

echo "📈 Statistiques:\n";
echo "  - Lignes: " . count($lines) . "\n";
echo "  - @if/@endif: $ifCount/$endifCount\n";
echo "  - @foreach/@endforeach: $foreachCount/$endforeachCount\n";
echo "  - @auth/@endauth: $authCount/$endauthCount\n";
echo "  - @section/@endsection: $sectionCount/$endsectionCount\n";
echo "  - Variables uniques: " . count($variables) . "\n";
echo "  - Routes uniques: " . count($routes) . "\n";
echo "  - Couleurs rouge: $redClasses\n";
echo "  - Couleurs vert: $greenClasses\n";

echo "\n" . str_repeat("=", 60) . "\n";

// Retourner le code d'erreur approprié
exit(empty($errors) ? 0 : 1);
