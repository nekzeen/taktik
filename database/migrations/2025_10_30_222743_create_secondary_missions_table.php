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
        Schema::create('secondary_missions', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('name')->unique(); // Ex: "BREAK THROUGH"
            $table->text('description'); // Description courte
            $table->text('full_text'); // Texte complet de la mission
            
            // Timing et conditions
            $table->text('when_drawn')->nullable(); // Condition "When Drawn"
            $table->text('when_condition'); // Condition de timing
            $table->string('timing')->default('any_battle_round'); // any_battle_round, etc.
            
            // Scoring
            $table->json('scoring_conditions'); // Conditions de scoring en JSON
            $table->integer('max_vp')->default(15); // Points de victoire maximum par tour
            
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
        Schema::dropIfExists('secondary_missions');
    }
};
