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
            $table->integer('player1_primary_points')->nullable();
            $table->integer('player1_secondary_points')->nullable();
            $table->boolean('player1_painting_points')->nullable();
            $table->integer('player2_primary_points')->nullable();
            $table->integer('player2_secondary_points')->nullable();
            $table->boolean('player2_painting_points')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropColumn([
                'player1_primary_points',
                'player1_secondary_points',
                'player1_painting_points',
                'player2_primary_points',
                'player2_secondary_points',
                'player2_painting_points',
            ]);
        });
    }
};
