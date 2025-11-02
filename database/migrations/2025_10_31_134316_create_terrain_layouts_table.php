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
        Schema::create('terrain_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Ex: "Terrain Layout 1"
            $table->string('slug')->unique(); // Ex: "terrain-layout-1"
            $table->text('description')->nullable();
            $table->string('image_url')->nullable(); // URL de l'image Wahapedia
            $table->string('image_path')->nullable(); // Chemin local du fichier téléchargé
            $table->integer('layout_number')->unique(); // Ex: 1, 2, 3...
            $table->string('source')->default('chapter-approved-2025-26'); // Source des données
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('layout_number');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terrain_layouts');
    }
};
