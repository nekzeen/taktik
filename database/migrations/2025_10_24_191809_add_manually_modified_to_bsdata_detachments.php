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
        Schema::table('bsdata_detachments', function (Blueprint $table) {
            $table->boolean('manually_modified')->default(false)->after('is_manual');
            $table->index('manually_modified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bsdata_detachments', function (Blueprint $table) {
            $table->dropIndex(['manually_modified']);
            $table->dropColumn('manually_modified');
        });
    }
};
