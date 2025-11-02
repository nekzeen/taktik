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
        Schema::create('tournament_mission_pools', function (Blueprint $table) {
            $table->id();
            $table->integer('pool_number')->unique(); // 1-20
            $table->string('name')->unique(); // Ex: "Mission Pool 1"
            $table->string('slug')->unique(); // Ex: "mission-pool-1"
            $table->text('description')->nullable();
            $table->unsignedBigInteger('primary_mission_id')->nullable();
            $table->string('deployment_mode')->nullable(); // Ex: "Hammer and Anvil", "Dawn of War", etc.
            $table->unsignedBigInteger('terrain_layout_id')->nullable();
            $table->boolean('use_twist_deck')->default(false);
            $table->string('source')->default('chapter-approved-2025-26');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('pool_number');
            $table->index('is_active');
            
            // Foreign keys
            $table->foreign('primary_mission_id')->references('id')->on('primary_missions')->nullOnDelete();
            $table->foreign('terrain_layout_id')->references('id')->on('terrain_layouts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_mission_pools');
    }
};
