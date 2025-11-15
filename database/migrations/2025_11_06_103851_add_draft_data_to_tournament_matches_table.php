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
            if (!Schema::hasColumn('tournament_matches', 'draft_scores')) {
                $table->json('draft_scores')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('tournament_matches', 'draft_tactical_state_player1')) {
                $table->json('draft_tactical_state_player1')->nullable()->after('draft_scores');
            }

            if (!Schema::hasColumn('tournament_matches', 'draft_tactical_state_player2')) {
                $table->json('draft_tactical_state_player2')->nullable()->after('draft_tactical_state_player1');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropColumn(['draft_scores', 'draft_tactical_state_player1', 'draft_tactical_state_player2']);
        });
    }
};
