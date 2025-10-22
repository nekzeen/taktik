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
        Schema::create('bsdata_units', function (Blueprint $table) {
            $table->id();
            $table->string('bsdata_id')->unique(); // ID depuis BSData
            $table->foreignId('faction_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type')->nullable(); // HQ, Troops, Elites, etc.
            $table->integer('points_min')->nullable();
            $table->integer('points_max')->nullable();
            $table->json('keywords')->nullable(); // Mots-clés de l'unité
            $table->json('abilities')->nullable(); // Capacités
            $table->json('wargear')->nullable(); // Équipement
            $table->text('description')->nullable();
            $table->json('raw_data')->nullable(); // Données brutes XML/JSON
            $table->timestamps();
            
            $table->index('name');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bsdata_units');
    }
};
