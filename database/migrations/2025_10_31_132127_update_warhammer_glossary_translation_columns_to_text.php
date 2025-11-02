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
        Schema::table('warhammer_glossary', function (Blueprint $table) {
            $table->text('french_translation')->nullable()->change();
            $table->text('german_translation')->nullable()->change();
            $table->text('spanish_translation')->nullable()->change();
            $table->text('italian_translation')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warhammer_glossary', function (Blueprint $table) {
            $table->string('french_translation', 255)->nullable()->change();
            $table->string('german_translation', 255)->nullable()->change();
            $table->string('spanish_translation', 255)->nullable()->change();
            $table->string('italian_translation', 255)->nullable()->change();
        });
    }
};
