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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faction_id')->constrained()->onDelete('cascade');
            $table->string('bsdata_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('description_fr')->nullable();
            $table->integer('points')->nullable();
            $table->enum('translation_status', ['pending', 'auto', 'reviewed', 'approved'])->default('pending');
            $table->timestamps();
            
            $table->index('faction_id');
            $table->index('bsdata_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
