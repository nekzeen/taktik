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
        Schema::create('tournament_mission_pool_secondary_missions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tournament_mission_pool_id');
            $table->unsignedBigInteger('secondary_mission_id');
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->timestamps();
            
            // Foreign keys with shorter names
            $table->foreign('tournament_mission_pool_id', 'tmpsm_pool_fk')
                ->references('id')->on('tournament_mission_pools')->cascadeOnDelete();
            $table->foreign('secondary_mission_id', 'tmpsm_secondary_fk')
                ->references('id')->on('secondary_missions')->cascadeOnDelete();
            
            // Unique constraint
            $table->unique(['tournament_mission_pool_id', 'secondary_mission_id'], 'tmpsm_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_mission_pool_secondary_missions');
    }
};
