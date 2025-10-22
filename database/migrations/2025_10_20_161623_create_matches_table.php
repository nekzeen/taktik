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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['tournament', 'friendly'])->default('tournament');
            $table->integer('round')->nullable();
            $table->foreignId('player1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('player2_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('player1_score')->nullable();
            $table->integer('player2_score')->nullable();
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('played_at')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
            
            $table->index(['tournament_id', 'round']);
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
