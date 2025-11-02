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
        Schema::table('army_lists', function (Blueprint $table) {
            // Ajouter la colonne detachment_id
            $table->unsignedBigInteger('detachment_id')->nullable()->after('faction_id');
            
            // Ajouter la clé étrangère
            $table->foreign('detachment_id')
                ->references('id')
                ->on('detachments')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('army_lists', function (Blueprint $table) {
            // Supprimer la clé étrangère
            $table->dropForeign(['detachment_id']);
            // Supprimer la colonne
            $table->dropColumn('detachment_id');
        });
    }
};
