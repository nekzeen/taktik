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
        Schema::create('primary_missions', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('name')->unique(); // Ex: "LINCHPIN"
            $table->text('description'); // Description courte
            $table->text('full_text'); // Texte complet de la mission
            
            // Timing
            $table->text('when_condition'); // Ex: "End of the Command phase (or the end of your turn if it is the fifth battle round and you are going second)"
            $table->string('timing')->default('second_battle_round_onwards'); // second_battle_round_onwards, etc.
            
            // Scoring
            $table->json('scoring_conditions'); // Conditions de scoring en JSON
            $table->integer('max_vp')->default(15); // Points de victoire maximum par tour
            
            // Métadonnées
            $table->string('edition')->default('10ed'); // 10ed, etc.
            $table->string('source')->default('chapter-approved-2025-26'); // Source de la mission
            $table->string('slug')->unique(); // Pour les URLs
            $table->boolean('is_active')->default(true);
            
            // Les traductions sont gérées via la table 'translations'
            // avec resource_type='PrimaryMission' et resource_id=id
            
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
        Schema::dropIfExists('primary_missions');
    }
};
