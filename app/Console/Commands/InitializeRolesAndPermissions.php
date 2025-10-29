<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class InitializeRolesAndPermissions extends Command
{
    protected $signature = 'init:roles-permissions';
    protected $description = 'Initialiser les rôles et permissions';

    public function handle(): int
    {
        $this->line('Initialisation des rôles et permissions...');

        // Créer les permissions
        $permissions = [
            'manage-tournaments',
            'join-tournaments',
            'manage-army-lists',
            'validate-army-lists',
            'upload-army-list',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
            $this->line("✅ Permission créée: {$permission}");
        }

        // Créer les rôles
        $roles = [
            'super-admin' => ['manage-tournaments', 'join-tournaments', 'manage-army-lists', 'validate-army-lists', 'upload-army-list'],
            'admin' => ['manage-tournaments', 'join-tournaments', 'manage-army-lists', 'validate-army-lists', 'upload-army-list'],
            'moderator' => ['manage-tournaments', 'join-tournaments', 'validate-army-lists'],
            'player' => ['manage-tournaments', 'join-tournaments', 'upload-army-list'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
            $this->line("✅ Rôle créé: {$roleName}");
        }

        $this->line('');
        $this->line('✅ Rôles et permissions initialisés avec succès !');
        return 0;
    }
}
