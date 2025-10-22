<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@wh40k.local',
            'password' => Hash::make('password'),
            'consent_at' => now(),
            'last_activity_at' => now(),
        ]);
        $superAdmin->assignRole('super-admin');

        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin2@wh40k.local',
            'password' => Hash::make('password'),
            'consent_at' => now(),
            'last_activity_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Create Moderator
        $moderator = User::create([
            'name' => 'Moderator User',
            'email' => 'moderator@wh40k.local',
            'password' => Hash::make('password'),
            'consent_at' => now(),
            'last_activity_at' => now(),
        ]);
        $moderator->assignRole('moderator');

        // Create Player
        $player = User::create([
            'name' => 'Player User',
            'email' => 'player@wh40k.local',
            'password' => Hash::make('password'),
            'consent_at' => now(),
            'last_activity_at' => now(),
        ]);
        $player->assignRole('player');
    }
}
