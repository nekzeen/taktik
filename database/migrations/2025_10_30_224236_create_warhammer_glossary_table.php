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
        Schema::create('warhammer_glossary', function (Blueprint $table) {
            $table->id();
            
            // Terme anglais (clé unique)
            $table->string('english_term')->unique();
            
            // Catégorie (ex: "unit", "ability", "keyword", "condition", "action")
            $table->string('category')->default('general');
            
            // Contexte (ex: "primary_mission", "secondary_mission", "general")
            $table->string('context')->default('general');
            
            // Traductions par langue
            $table->string('french_translation')->nullable();
            $table->string('german_translation')->nullable();
            $table->string('spanish_translation')->nullable();
            $table->string('italian_translation')->nullable();
            
            // Description/notes
            $table->text('description')->nullable();
            
            // Exemple d'utilisation
            $table->text('example')->nullable();
            
            // Statut d'approbation
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Compteur d'utilisation (pour les termes les plus utilisés)
            $table->integer('usage_count')->default(0);
            
            $table->timestamps();
            $table->index('category');
            $table->index('context');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warhammer_glossary');
    }
};
