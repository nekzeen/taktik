<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncUserPermissions extends Command
{
    protected $signature = 'users:sync-permissions';
    protected $description = 'Synchronise les permissions des utilisateurs selon leurs rôles';

    public function handle()
    {
        $this->info('Synchronisation des permissions des utilisateurs...');
        $this->newLine();

        // Définir les permissions par rôle
        $rolePermissions = [
            'super-admin' => [
                'manage-users', 'manage-tournaments', 'manage-army-lists', 'manage-translations',
                'manage-pages', 'manage-menus', 'import-bsdata', 'view-audit-logs',
                'validate-army-lists', 'moderate-matches', 'view-calendar', 'create-tournaments',
                'join-tournaments', 'upload-army-list', 'create-match-request', 'manage-own-calendar'
            ],
            'admin' => [
                'manage-users', 'manage-tournaments', 'manage-army-lists', 'manage-translations',
                'manage-pages', 'manage-menus', 'import-bsdata', 'validate-army-lists',
                'moderate-matches', 'view-calendar'
            ],
            'moderator' => [
                'validate-army-lists', 'moderate-matches', 'view-calendar'
            ],
            'player' => [
                'join-tournaments', 'upload-army-list', 'create-match-request',
                'manage-own-calendar', 'manage-tournaments'
            ],
            'visitor' => []
        ];

        $synced = 0;
        $errors = 0;

        foreach ($rolePermissions as $role => $permissions) {
            $users = User::role($role)->get();

            foreach ($users as $user) {
                try {
                    // Retirer les permissions actuelles
                    $user->permissions()->detach();

                    // Ajouter les nouvelles permissions
                    foreach ($permissions as $permission) {
                        $user->givePermissionTo($permission);
                    }

                    $synced++;
                    $this->line("✓ {$user->name} ({$role})");
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("✗ Erreur pour {$user->name}: {$e->getMessage()}");
                }
            }
        }

        $this->newLine();
        $this->info("Synchronisation terminée!");
        $this->info("Utilisateurs synchronisés: $synced");
        if ($errors > 0) {
            $this->error("Erreurs: $errors");
        }
    }
}
