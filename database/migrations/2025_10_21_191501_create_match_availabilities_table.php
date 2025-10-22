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
        Schema::create('match_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_match_id')->constrained('tournament_matches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['single', 'period'])->default('single'); // ponctuelle ou période
            $table->dateTime('available_at')->nullable(); // Pour disponibilité ponctuelle
            $table->dateTime('available_from')->nullable(); // Début de période
            $table->dateTime('available_to')->nullable(); // Fin de période
            $table->text('notes')->nullable();
            $table->boolean('opponent_notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            // Index pour les recherches
            $table->index(['tournament_match_id', 'user_id']);
            $table->index('available_at');
            $table->index(['available_from', 'available_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_availabilities');
    }
};
