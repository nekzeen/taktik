<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer définitivement les utilisateurs soft-deleted
        // Cela libère les emails réservés
        DB::table('users')
            ->whereNotNull('deleted_at')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Impossible de restaurer les utilisateurs supprimés définitivement
        // Cette migration est irréversible
    }
};
