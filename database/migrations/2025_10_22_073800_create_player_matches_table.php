<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('opponent_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('type', ['competitive', 'narrative'])->default('competitive');
            $table->integer('army_points')->default(2000);
            $table->string('faction')->nullable();
            $table->string('detachment')->nullable();
            $table->text('notes')->nullable();
            $table->string('city');
            $table->string('department');
            $table->enum('availability_type', ['single', 'period'])->default('single');
            $table->dateTime('available_at')->nullable();
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_to')->nullable();
            $table->enum('status', ['open', 'confirmed', 'completed', 'cancelled'])->default('open');
            $table->integer('creator_score')->nullable();
            $table->integer('opponent_score')->nullable();
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_draw')->default(false);
            $table->dateTime('played_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_matches');
    }
};
