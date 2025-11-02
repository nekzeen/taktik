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
        Schema::dropIfExists('primary_missions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer la table si on rollback
        Schema::create('primary_missions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description');
            $table->text('full_text');
            $table->text('when_condition');
            $table->string('timing')->default('second_battle_round_onwards');
            $table->json('scoring_conditions');
            $table->integer('max_vp')->default(15);
            $table->string('edition')->default('10ed');
            $table->string('source')->default('chapter-approved-2025-26');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->text('name_fr')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('full_text_fr')->nullable();
            $table->text('when_condition_fr')->nullable();
            $table->timestamps();
            $table->index('source');
            $table->index('is_active');
        });
    }
};
