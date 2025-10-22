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
        Schema::create('bsdata_detachments', function (Blueprint $table) {
            $table->id();
            $table->string('bsdata_id')->unique();
            $table->foreignId('faction_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('rules')->nullable(); // Règles du détachement
            $table->json('stratagems')->nullable(); // Stratagèmes
            $table->json('enhancements')->nullable(); // Améliorations
            $table->json('raw_data')->nullable();
            $table->timestamps();
            
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bsdata_detachments');
    }
};
