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
        Schema::create('primary_mission_sections', function (Blueprint $table) {
            $table->id();
            
            // Relation à la mission
            $table->unsignedBigInteger('primary_mission_id');
            $table->foreign('primary_mission_id')
                ->references('id')
                ->on('primary_missions')
                ->onDelete('cascade');
            
            // Type de section
            $table->enum('type', ['action', 'scoring', 'objective'])->default('scoring');
            
            // Ordre d'affichage
            $table->integer('order')->default(0);
            
            // Titre de l'action (ex: "BURN OBJECTIVE")
            $table->string('title')->nullable();
            
            // Timing (ex: "STARTS", "COMPLETES", "WHEN")
            $table->string('timing')->nullable();
            
            // Contenu principal
            $table->text('content');
            
            // Conditions (JSON)
            $table->json('conditions')->nullable();
            
            // Points de victoire associés
            $table->integer('victory_points')->nullable();
            
            // Traductions
            $table->text('title_fr')->nullable();
            $table->text('timing_fr')->nullable();
            $table->text('content_fr')->nullable();
            
            $table->timestamps();
            $table->index('primary_mission_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('primary_mission_sections');
    }
};
