<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Admin permissions
            'manage-users',
            'manage-tournaments',
            'manage-army-lists',
            'manage-translations',
            'manage-pages',
            'manage-menus',
            'import-bsdata',
            'view-audit-logs',
            
            // Moderator permissions
            'validate-army-lists',
            'moderate-matches',
            'view-calendar',
            
            // Player permissions
            'create-tournaments',
            'join-tournaments',
            'upload-army-list',
            'create-match-request',
            'manage-own-calendar',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin - all permissions
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'manage-users',
            'manage-tournaments',
            'manage-army-lists',
            'manage-translations',
            'manage-pages',
            'manage-menus',
            'import-bsdata',
            'validate-army-lists',
            'moderate-matches',
            'view-calendar',
        ]);

        // Moderator
        $moderator = Role::create(['name' => 'moderator']);
        $moderator->givePermissionTo([
            'validate-army-lists',
            'moderate-matches',
            'view-calendar',
        ]);

        // Player
        $player = Role::create(['name' => 'player']);
        $player->givePermissionTo([
            'join-tournaments',
            'upload-army-list',
            'create-match-request',
            'manage-own-calendar',
        ]);

        // Visitor (no permissions, read-only)
        Role::create(['name' => 'visitor']);
    }
}
