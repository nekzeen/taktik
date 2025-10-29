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
            $table->boolean('is_manual')->default(false)->after('raw_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bsdata_detachments', function (Blueprint $table) {
            $table->dropColumn('is_manual');
        });
    }
};
