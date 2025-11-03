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
            $table->foreignId('secondary_mission_id')->nullable()->after('primary_mission_id')->constrained('secondary_missions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_matches', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('player_matches_secondary_mission_id_foreign');
            $table->dropColumn('secondary_mission_id');
        });
    }
};
