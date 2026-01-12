<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('completed_at');
            $table->foreignId('scheduled_by_user_id')->nullable()->after('scheduled_at')->constrained('users');
            $table->foreignId('scheduled_from_user_id')->nullable()->after('scheduled_by_user_id')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scheduled_from_user_id');
            $table->dropConstrainedForeignId('scheduled_by_user_id');
            $table->dropColumn('scheduled_at');
        });
    }
};
