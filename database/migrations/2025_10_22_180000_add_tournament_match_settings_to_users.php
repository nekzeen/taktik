<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_create_tournaments')->default(true)->after('email_verified_at');
            $table->boolean('can_create_matches')->default(true)->after('can_create_tournaments');
            $table->integer('max_open_tournaments')->default(null)->nullable()->after('can_create_matches');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_create_tournaments', 'can_create_matches', 'max_open_tournaments']);
        });
    }
};
