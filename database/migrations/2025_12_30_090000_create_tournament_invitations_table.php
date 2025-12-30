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
        Schema::create('tournament_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->foreignId('invited_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('invited_by_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->string('message', 500)->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'invited_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_invitations');
    }
};
