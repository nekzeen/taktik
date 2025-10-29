#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  TEST COMPLET DU FLUX D'INSCRIPTION ET VÉRIFICATION D'EMAIL    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// 1. Créer un utilisateur
echo "📝 ÉTAPE 1 : Création d'un utilisateur\n";
echo "─────────────────────────────────────────\n";
$user = \App\Models\User::create([
    'name' => 'Test Verification ' . time(),
    'email' => 'test.verify.' . time() . '@gaelmorvan.fr',
    'password' => bcrypt('password123'),
]);
echo "✅ Utilisateur créé\n";
echo "   ID: {$user->id}\n";
echo "   Email: {$user->email}\n";
echo "   Nom: {$user->name}\n\n";

// 2. Vérifier que l'email n'est pas vérifié
echo "📋 ÉTAPE 2 : Vérification du statut initial\n";
echo "────────────────────────────────────────────\n";
echo "   Email vérifié: " . ($user->hasVerifiedEmail() ? '✅ OUI' : '❌ NON') . "\n";
echo "   Rôles: " . ($user->roles->isEmpty() ? '❌ AUCUN' : '✅ ' . $user->roles->pluck('name')->join(', ')) . "\n";
echo "   Permissions: " . ($user->permissions->isEmpty() ? '❌ AUCUNE' : '✅ ' . $user->permissions->pluck('name')->join(', ')) . "\n\n";

// 3. Déclencher l'événement Verified
echo "🔐 ÉTAPE 3 : Simulation de la vérification d'email\n";
echo "──────────────────────────────────────────────────\n";
$user->markEmailAsVerified();
event(new \Illuminate\Auth\Events\Verified($user));
echo "✅ Email marqué comme vérifié\n";
echo "✅ Événement Verified déclenché\n\n";

// 4. Vérifier le statut après vérification
echo "✨ ÉTAPE 4 : Vérification du statut après vérification\n";
echo "───────────────────────────────────────────────────────\n";
$user->refresh();
echo "   Email vérifié: " . ($user->hasVerifiedEmail() ? '✅ OUI' : '❌ NON') . "\n";
echo "   Rôles: " . ($user->roles->isEmpty() ? '❌ AUCUN' : '✅ ' . $user->roles->pluck('name')->join(', ')) . "\n";
echo "   Permissions: " . ($user->permissions->isEmpty() ? '❌ AUCUNE' : '✅ ' . $user->permissions->pluck('name')->join(', ')) . "\n\n";

// 5. Vérifier les permissions spécifiques
echo "🔑 ÉTAPE 5 : Vérification des permissions spécifiques\n";
echo "──────────────────────────────────────────────────────\n";
echo "   manage-tournaments: " . ($user->hasPermissionTo('manage-tournaments') ? '✅ OUI' : '❌ NON') . "\n";
echo "   join-tournaments: " . ($user->hasPermissionTo('join-tournaments') ? '✅ OUI' : '❌ NON') . "\n\n";

// 6. Vérifier la capacité à créer un tournoi
echo "🎮 ÉTAPE 6 : Vérification de la capacité à créer un tournoi\n";
echo "────────────────────────────────────────────────────────────\n";
$canCreate = $user->can('create', \App\Models\Tournament::class);
echo "   Peut créer un tournoi: " . ($canCreate ? '✅ OUI' : '❌ NON') . "\n\n";

// Résumé
echo "╔════════════════════════════════════════════════════════════════╗\n";
if ($user->hasVerifiedEmail() && $user->hasRole('player') && $canCreate) {
    echo "║  ✅ FLUX COMPLET RÉUSSI !                                      ║\n";
} else {
    echo "║  ❌ FLUX INCOMPLET OU ERREUR                                   ║\n";
}
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
