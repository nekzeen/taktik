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
        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->integer('round'); // Numéro du round (1, 2, 3, etc.)
            $table->integer('table_number')->nullable(); // Numéro de table
            
            // Joueur 1
            $table->foreignId('player1_id')->constrained('users');
            $table->foreignId('player1_army_list_id')->nullable()->constrained('army_lists');
            $table->integer('player1_score')->nullable();
            $table->integer('player1_victory_points')->nullable();
            
            // Joueur 2
            $table->foreignId('player2_id')->constrained('users');
            $table->foreignId('player2_army_list_id')->nullable()->constrained('army_lists');
            $table->integer('player2_score')->nullable();
            $table->integer('player2_victory_points')->nullable();
            
            // Résultat
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->foreignId('winner_id')->nullable()->constrained('users');
            $table->boolean('is_draw')->default(false);
            
            // Métadonnées
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['tournament_id', 'round']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_matches');
    }
};
