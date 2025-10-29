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
        // L'utilisateur 11 (soft-deleted) a l'email contact@gaelmorvan.fr
        // On change son email pour libérer contact@gaelmorvan.fr
        DB::table('users')
            ->where('id', 11)
            ->update(['email' => 'luc.petit.deleted@gaelmorvan.fr']);
        
        // Corriger l'email de l'utilisateur 21 : conctact@gaelmorvan.fr -> contact@gaelmorvan.fr
        DB::table('users')
            ->where('id', 21)
            ->update(['email' => 'contact@gaelmorvan.fr']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir aux emails précédents
        DB::table('users')
            ->where('id', 11)
            ->update(['email' => 'contact@gaelmorvan.fr']);
        
        DB::table('users')
            ->where('id', 21)
            ->update(['email' => 'conctact@gaelmorvan.fr']);
    }
};
