<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_matches', function (Blueprint $table) {
            $table->integer('creator_victory_points')->nullable()->after('creator_score');
            $table->integer('opponent_victory_points')->nullable()->after('opponent_score');
        });
    }

    public function down(): void
    {
        Schema::table('player_matches', function (Blueprint $table) {
            $table->dropColumn(['creator_victory_points', 'opponent_victory_points']);
        });
    }
};
