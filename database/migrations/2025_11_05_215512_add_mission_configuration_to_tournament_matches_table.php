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
            $table->foreignId('terrain_layout_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('twist_mission_id')->nullable()->constrained()->onDelete('set null');
            $table->string('deployment_mode')->nullable();
            $table->string('setup_mode')->nullable();
            $table->boolean('is_setup_complete')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['terrain_layout_id']);
            $table->dropForeignKeyIfExists(['twist_mission_id']);
            $table->dropColumn(['terrain_layout_id', 'twist_mission_id', 'deployment_mode', 'setup_mode', 'is_setup_complete']);
        });
    }
};
