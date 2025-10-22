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
        Schema::create('factions', function (Blueprint $table) {
            $table->id();
            $table->string('bsdata_id')->unique();
            $table->string('name');
            $table->string('name_fr')->nullable();
            $table->string('version')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
            
            $table->index('bsdata_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factions');
    }
};
