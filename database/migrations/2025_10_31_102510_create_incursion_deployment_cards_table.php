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
        Schema::create('incursion_deployment_cards', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('name')->unique(); // Ex: "AMBUSH"
            $table->text('description'); // Description courte
            $table->text('full_text'); // Texte complet de la carte
            
            // Contenu de la carte
            $table->text('card_content')->nullable(); // Contenu détaillé de la carte
            $table->text('rules')->nullable(); // Règles spéciales
            
            // Images
            $table->string('image_url')->nullable(); // URL de l'image originale
            $table->string('image_path')->nullable(); // Chemin local de l'image (storage/app/public/incursion-cards/)
            $table->string('image_filename')->nullable(); // Nom du fichier image
            
            // Métadonnées
            $table->string('edition')->default('10ed'); // 10ed, etc.
            $table->string('source')->default('chapter-approved-2025-26'); // Source de la carte
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
        Schema::dropIfExists('incursion_deployment_cards');
    }
};
