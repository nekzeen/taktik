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
            $table->foreignId('score_recorder_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('score_recorder_selected_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['score_recorder_id']);
            $table->dropColumn(['score_recorder_id', 'score_recorder_selected_at']);
        });
    }
};
