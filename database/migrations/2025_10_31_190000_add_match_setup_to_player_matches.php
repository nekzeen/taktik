<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('player_matches', function (Blueprint $table) {
            // Missions et terrain
            $table->foreignId('primary_mission_id')->nullable()->after('opponent_score')->constrained('primary_missions')->nullOnDelete();
            $table->foreignId('terrain_layout_id')->nullable()->after('primary_mission_id')->constrained('terrain_layouts')->nullOnDelete();
            $table->foreignId('twist_mission_id')->nullable()->after('terrain_layout_id')->constrained('twist_missions')->nullOnDelete();
            
            // Mode de tirage
            $table->enum('setup_mode', ['random', 'manual'])->default('random')->after('twist_mission_id');
            
            // État du tirage
            $table->boolean('is_setup_complete')->default(false)->after('setup_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_matches', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('player_matches_primary_mission_id_foreign');
            $table->dropForeignKeyIfExists('player_matches_terrain_layout_id_foreign');
            $table->dropForeignKeyIfExists('player_matches_twist_mission_id_foreign');
            
            $table->dropColumn([
                'primary_mission_id',
                'terrain_layout_id',
                'twist_mission_id',
                'setup_mode',
                'is_setup_complete',
            ]);
        });
    }
};
