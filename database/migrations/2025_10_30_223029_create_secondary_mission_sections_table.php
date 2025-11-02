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
        Schema::create('secondary_mission_sections', function (Blueprint $table) {
            $table->id();
            
            // Relation à la mission
            $table->unsignedBigInteger('secondary_mission_id');
            $table->foreign('secondary_mission_id')
                ->references('id')
                ->on('secondary_missions')
                ->onDelete('cascade');
            
            // Type de section
            $table->enum('type', ['condition', 'scoring', 'note'])->default('scoring');
            
            // Ordre d'affichage
            $table->integer('order')->default(0);
            
            // Titre de la section
            $table->string('title')->nullable();
            
            // Contenu principal
            $table->text('content');
            
            // Points de victoire associés
            $table->integer('victory_points')->nullable();
            
            // Conditions (JSON)
            $table->json('conditions')->nullable();
            
            $table->timestamps();
            $table->index('secondary_mission_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secondary_mission_sections');
    }
};
