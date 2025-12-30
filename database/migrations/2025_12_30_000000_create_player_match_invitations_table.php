<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_match_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invited_by_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted', 'declined', 'cancelled'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->unique(['player_match_id', 'invited_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_match_invitations');
    }
};
