<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Auth\Events\Verified;

class TestEmailVerificationFlow extends Command
{
    protected $signature = 'test:email-verification-flow';
    protected $description = 'Test le flux complet d\'inscription et vérification d\'email';

    public function handle(): int
    {
        $this->line('');
        $this->line('╔════════════════════════════════════════════════════════════════╗');
        $this->line('║  TEST COMPLET DU FLUX D\'INSCRIPTION ET VÉRIFICATION D\'EMAIL    ║');
        $this->line('╚════════════════════════════════════════════════════════════════╝');
        $this->line('');

        // 1. Créer un utilisateur
        $this->line('📝 ÉTAPE 1 : Création d\'un utilisateur');
        $this->line('─────────────────────────────────────────');
        $user = User::create([
            'name' => 'Test Verification ' . time(),
            'email' => 'test.verify.' . time() . '@gaelmorvan.fr',
            'password' => bcrypt('password123'),
        ]);
        $this->line('✅ Utilisateur créé');
        $this->line("   ID: {$user->id}");
        $this->line("   Email: {$user->email}");
        $this->line("   Nom: {$user->name}");
        $this->line('');

        // 2. Vérifier que l'email n'est pas vérifié
        $this->line('📋 ÉTAPE 2 : Vérification du statut initial');
        $this->line('────────────────────────────────────────────');
        $emailVerified = $user->hasVerifiedEmail() ? '✅ OUI' : '❌ NON';
        $roles = $user->roles->isEmpty() ? '❌ AUCUN' : '✅ ' . $user->roles->pluck('name')->join(', ');
        $permissions = $user->permissions->isEmpty() ? '❌ AUCUNE' : '✅ ' . $user->permissions->pluck('name')->join(', ');
        $this->line("   Email vérifié: {$emailVerified}");
        $this->line("   Rôles: {$roles}");
        $this->line("   Permissions: {$permissions}");
        $this->line('');

        // 3. Déclencher l'événement Verified
        $this->line('🔐 ÉTAPE 3 : Simulation de la vérification d\'email');
        $this->line('──────────────────────────────────────────────────');
        $user->markEmailAsVerified();
        event(new Verified($user));
        $this->line('✅ Email marqué comme vérifié');
        $this->line('✅ Événement Verified déclenché');
        $this->line('');

        // 4. Vérifier le statut après vérification
        $this->line('✨ ÉTAPE 4 : Vérification du statut après vérification');
        $this->line('───────────────────────────────────────────────────────');
        $user->refresh();
        $emailVerified = $user->hasVerifiedEmail() ? '✅ OUI' : '❌ NON';
        $roles = $user->roles->isEmpty() ? '❌ AUCUN' : '✅ ' . $user->roles->pluck('name')->join(', ');
        $permissions = $user->permissions->isEmpty() ? '❌ AUCUNE' : '✅ ' . $user->permissions->pluck('name')->join(', ');
        $this->line("   Email vérifié: {$emailVerified}");
        $this->line("   Rôles: {$roles}");
        $this->line("   Permissions: {$permissions}");
        $this->line('');

        // 5. Vérifier les permissions spécifiques
        $this->line('🔑 ÉTAPE 5 : Vérification des permissions spécifiques');
        $this->line('──────────────────────────────────────────────────────');
        $manageTournaments = $user->hasPermissionTo('manage-tournaments') ? '✅ OUI' : '❌ NON';
        $joinTournaments = $user->hasPermissionTo('join-tournaments') ? '✅ OUI' : '❌ NON';
        $this->line("   manage-tournaments: {$manageTournaments}");
        $this->line("   join-tournaments: {$joinTournaments}");
        $this->line('');

        // 6. Vérifier la capacité à créer un tournoi
        $this->line('🎮 ÉTAPE 6 : Vérification de la capacité à créer un tournoi');
        $this->line('────────────────────────────────────────────────────────────');
        $canCreate = $user->can('create', \App\Models\Tournament::class) ? '✅ OUI' : '❌ NON';
        $this->line("   Peut créer un tournoi: {$canCreate}");
        $this->line('');

        // Résumé
        $this->line('╔════════════════════════════════════════════════════════════════╗');
        if ($user->hasVerifiedEmail() && $user->hasRole('player') && $user->can('create', \App\Models\Tournament::class)) {
            $this->line('║  ✅ FLUX COMPLET RÉUSSI !                                      ║');
            $this->line('╚════════════════════════════════════════════════════════════════╝');
            $this->line('');
            return 0;
        } else {
            $this->line('║  ❌ FLUX INCOMPLET OU ERREUR                                   ║');
            $this->line('╚════════════════════════════════════════════════════════════════╝');
            $this->line('');
            return 1;
        }
    }
}
