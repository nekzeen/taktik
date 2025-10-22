<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("UPDATE factions SET name = TRIM(REPLACE(name, ' Library', '')) WHERE name LIKE '% Library'");
        DB::statement("UPDATE factions SET name_fr = TRIM(REPLACE(name_fr, ' Library', '')) WHERE name_fr LIKE '% Library'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pas de rollback nécessaire pour ce nettoyage
    }
};
