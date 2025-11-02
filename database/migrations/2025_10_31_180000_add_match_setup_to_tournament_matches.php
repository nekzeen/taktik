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
        Schema::table('tournament_matches', function (Blueprint $table) {
            // Missions et terrain
            $table->foreignId('terrain_layout_id')->nullable()->after('primary_mission_id')->constrained('terrain_layouts')->nullOnDelete();
            $table->foreignId('twist_mission_id')->nullable()->after('terrain_layout_id')->constrained('twist_missions')->nullOnDelete();
            
            // Mode de tirage
            $table->enum('setup_mode', ['random', 'manual'])->default('random')->after('twist_mission_id');
            
            // Missions asymétriques (pour les matchs asymétriques)
            $table->foreignId('asymmetric_primary_mission_id')->nullable()->after('setup_mode')->constrained('asymmetric_primary_missions')->nullOnDelete();
            
            // État du tirage
            $table->boolean('is_setup_complete')->default(false)->after('asymmetric_primary_mission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('tournament_matches_terrain_layout_id_foreign');
            $table->dropForeignKeyIfExists('tournament_matches_twist_mission_id_foreign');
            $table->dropForeignKeyIfExists('tournament_matches_asymmetric_primary_mission_id_foreign');
            
            $table->dropColumn([
                'terrain_layout_id',
                'twist_mission_id',
                'setup_mode',
                'asymmetric_primary_mission_id',
                'is_setup_complete',
            ]);
        });
    }
};
