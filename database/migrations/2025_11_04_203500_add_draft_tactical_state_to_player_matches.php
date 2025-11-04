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
            $table->json('draft_tactical_state_creator')->nullable()->after('draft_scores');
            $table->json('draft_tactical_state_opponent')->nullable()->after('draft_tactical_state_creator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_matches', function (Blueprint $table) {
            $table->dropColumn(['draft_tactical_state_creator', 'draft_tactical_state_opponent']);
        });
    }
};
