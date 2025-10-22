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
        Schema::create('player_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['single', 'period'])->default('single');
            $table->dateTime('available_at')->nullable(); // Pour disponibilité ponctuelle
            $table->dateTime('available_from')->nullable(); // Début de période
            $table->dateTime('available_to')->nullable(); // Fin de période
            $table->text('notes')->nullable();
            $table->timestamps();

            // Un joueur ne peut avoir qu'une seule disponibilité active par tournoi
            $table->unique(['tournament_id', 'user_id']);
            
            // Index pour les recherches
            $table->index('available_at');
            $table->index(['available_from', 'available_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_availabilities');
    }
};
