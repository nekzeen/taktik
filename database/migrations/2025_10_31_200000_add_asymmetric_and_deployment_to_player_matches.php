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
            // Mission asymétrique
            $table->foreignId('asymmetric_primary_mission_id')->nullable()->after('twist_mission_id')->constrained('asymmetric_primary_missions')->nullOnDelete();
            
            // Zone de déploiement
            $table->string('deployment_mode')->nullable()->after('asymmetric_primary_mission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_matches', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('player_matches_asymmetric_primary_mission_id_foreign');
            $table->dropColumn([
                'asymmetric_primary_mission_id',
                'deployment_mode',
            ]);
        });
    }
};
