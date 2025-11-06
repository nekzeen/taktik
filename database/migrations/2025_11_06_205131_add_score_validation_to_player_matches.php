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
            $table->boolean('creator_score_validated')->default(false)->after('status');
            $table->boolean('opponent_score_validated')->default(false)->after('creator_score_validated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_matches', function (Blueprint $table) {
            $table->dropColumn(['creator_score_validated', 'opponent_score_validated']);
        });
    }
};
