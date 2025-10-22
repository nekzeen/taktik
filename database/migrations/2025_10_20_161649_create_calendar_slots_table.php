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
        Schema::create('calendar_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->datetime('start_at');
            $table->datetime('end_at');
            $table->enum('type', ['availability', 'match', 'tournament'])->default('availability');
            $table->enum('status', ['proposed', 'confirmed', 'cancelled'])->default('proposed');
            $table->foreignId('match_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('tournament_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('timezone')->default('Europe/Paris');
            $table->timestamps();
            
            $table->index(['user_id', 'start_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_slots');
    }
};
