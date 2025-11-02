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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->text('source_text');
            $table->text('translated_text');
            $table->string('source_language', 5);
            $table->string('target_language', 5);
            $table->string('translator', 20)->default('deepl');
            $table->boolean('reviewed')->default(false);
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Index pour les recherches
            $table->index(['source_language', 'target_language']);
            $table->index('translator');
            $table->index('reviewed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
