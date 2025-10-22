<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            // Supprimer les contraintes existantes
            $table->dropForeign(['player1_army_list_id']);
            $table->dropForeign(['player2_army_list_id']);
            
            // Recréer avec cascadeOnDelete
            $table->foreign('player1_army_list_id')
                ->references('id')
                ->on('army_lists')
                ->cascadeOnDelete()
                ->change();
            
            $table->foreign('player2_army_list_id')
                ->references('id')
                ->on('army_lists')
                ->cascadeOnDelete()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropForeign(['player1_army_list_id']);
            $table->dropForeign(['player2_army_list_id']);
            
            $table->foreign('player1_army_list_id')
                ->references('id')
                ->on('army_lists');
            
            $table->foreign('player2_army_list_id')
                ->references('id')
                ->on('army_lists');
        });
    }
};
