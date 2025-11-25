<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RuleDiscussionCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rule_discussion_categories')->insert([
            ['name' => 'Règles par Faction', 'slug' => 'regles-par-faction', 'description' => 'Questions sur les règles spécifiques à chaque faction', 'order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Règles Générales', 'slug' => 'regles-generales', 'description' => 'Questions sur les règles générales de Warhammer 40K', 'order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Missions Primaires', 'slug' => 'missions-primaires', 'description' => 'Questions sur les missions primaires', 'order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Missions Secondaires', 'slug' => 'missions-secondaires', 'description' => 'Questions sur les missions secondaires', 'order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Péripéties', 'slug' => 'peripeties', 'description' => 'Questions sur les péripéties (Twist Deck)', 'order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('rule_discussion_settings')->insert([
            ['id' => 1, 'max_images_per_discussion' => 1, 'enable_notifications' => true, 'enable_moderation' => true, 'auto_archive_days' => 90, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
