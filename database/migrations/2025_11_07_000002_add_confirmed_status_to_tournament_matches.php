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
        Schema::table('tournament_matches', function (Blueprint $table) {
            // Modifier l'ENUM pour ajouter 'confirmed'
            $table->enum('status', ['pending', 'in_progress', 'confirmed', 'completed'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            // Revenir à l'ENUM original
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending')->change();
        });
    }
};
