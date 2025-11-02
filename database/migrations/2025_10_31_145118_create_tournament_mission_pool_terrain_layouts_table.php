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
        Schema::create('tournament_mission_pool_terrain_layouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tournament_mission_pool_id');
            $table->unsignedBigInteger('terrain_layout_id');
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->timestamps();
            
            // Foreign keys with shorter names
            $table->foreign('tournament_mission_pool_id', 'tmptl_pool_fk')
                ->references('id')->on('tournament_mission_pools')->cascadeOnDelete();
            $table->foreign('terrain_layout_id', 'tmptl_terrain_fk')
                ->references('id')->on('terrain_layouts')->cascadeOnDelete();
            
            // Unique constraint
            $table->unique(['tournament_mission_pool_id', 'terrain_layout_id'], 'tmptl_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_mission_pool_terrain_layouts');
    }
};
