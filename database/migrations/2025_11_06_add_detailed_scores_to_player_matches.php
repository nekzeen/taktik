<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_matches', function (Blueprint $table) {
            // Ajouter les colonnes pour les points détaillés du créateur
            $table->integer('creator_primary_points')->nullable()->after('creator_score');
            $table->integer('creator_secondary_points')->nullable()->after('creator_primary_points');
            $table->boolean('creator_painting_points')->default(false)->after('creator_secondary_points');
            
            // Ajouter les colonnes pour les points détaillés de l'adversaire
            $table->integer('opponent_primary_points')->nullable()->after('opponent_score');
            $table->integer('opponent_secondary_points')->nullable()->after('opponent_primary_points');
            $table->boolean('opponent_painting_points')->default(false)->after('opponent_secondary_points');
            
            // Ajouter les colonnes pour les victory points
            $table->integer('creator_victory_points')->nullable()->after('opponent_painting_points');
            $table->integer('opponent_victory_points')->nullable()->after('creator_victory_points');
        });
    }

    public function down(): void
    {
        Schema::table('player_matches', function (Blueprint $table) {
            $table->dropColumn([
                'creator_primary_points',
                'creator_secondary_points',
                'creator_painting_points',
                'opponent_primary_points',
                'opponent_secondary_points',
                'opponent_painting_points',
                'creator_victory_points',
                'opponent_victory_points',
            ]);
        });
    }
};
