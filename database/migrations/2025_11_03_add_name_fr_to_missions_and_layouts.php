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
        // Ajouter name_fr à primary_missions
        if (Schema::hasTable('primary_missions') && !Schema::hasColumn('primary_missions', 'name_fr')) {
            Schema::table('primary_missions', function (Blueprint $table) {
                $table->string('name_fr')->nullable()->after('name');
            });
        }

        // Ajouter name_fr à secondary_missions
        if (Schema::hasTable('secondary_missions') && !Schema::hasColumn('secondary_missions', 'name_fr')) {
            Schema::table('secondary_missions', function (Blueprint $table) {
                $table->string('name_fr')->nullable()->after('name');
            });
        }

        // Ajouter name_fr à terrain_layouts
        if (Schema::hasTable('terrain_layouts') && !Schema::hasColumn('terrain_layouts', 'name_fr')) {
            Schema::table('terrain_layouts', function (Blueprint $table) {
                $table->string('name_fr')->nullable()->after('name');
            });
        }

        // Ajouter name_fr à twist_missions
        if (Schema::hasTable('twist_missions') && !Schema::hasColumn('twist_missions', 'name_fr')) {
            Schema::table('twist_missions', function (Blueprint $table) {
                $table->string('name_fr')->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('primary_missions') && Schema::hasColumn('primary_missions', 'name_fr')) {
            Schema::table('primary_missions', function (Blueprint $table) {
                $table->dropColumn('name_fr');
            });
        }

        if (Schema::hasTable('secondary_missions') && Schema::hasColumn('secondary_missions', 'name_fr')) {
            Schema::table('secondary_missions', function (Blueprint $table) {
                $table->dropColumn('name_fr');
            });
        }

        if (Schema::hasTable('terrain_layouts') && Schema::hasColumn('terrain_layouts', 'name_fr')) {
            Schema::table('terrain_layouts', function (Blueprint $table) {
                $table->dropColumn('name_fr');
            });
        }

        if (Schema::hasTable('twist_missions') && Schema::hasColumn('twist_missions', 'name_fr')) {
            Schema::table('twist_missions', function (Blueprint $table) {
                $table->dropColumn('name_fr');
            });
        }
    }
};
