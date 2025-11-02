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
        Schema::create('asymmetric_primary_missions', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('name')->unique(); // Ex: "ASSASSINATE"
            $table->text('description'); // Description courte
            $table->text('full_text'); // Texte complet de la mission
            
            // Objectives et conditions
            $table->text('objectives'); // Objectifs de la mission
            $table->text('attacker_objective')->nullable(); // Objectif de l'attaquant
            $table->text('defender_objective')->nullable(); // Objectif du défenseur
            $table->text('timing')->nullable(); // Timing de la mission
            
            // Scoring
            $table->integer('max_vp')->default(0); // Points de victoire max
            $table->text('scoring_conditions')->nullable(); // Conditions de scoring
            
            // Métadonnées
            $table->string('edition')->default('10ed'); // 10ed, etc.
            $table->string('source')->default('chapter-approved-2025-26'); // Source de la mission
            $table->string('slug')->unique(); // Pour les URLs
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->index('source');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asymmetric_primary_missions');
    }
};
