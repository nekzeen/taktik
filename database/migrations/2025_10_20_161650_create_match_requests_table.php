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
        Schema::create('match_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('opponent_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->datetime('proposed_at');
            $table->enum('status', ['open', 'accepted', 'rejected', 'cancelled'])->default('open');
            $table->foreignId('match_id')->nullable()->constrained()->onDelete('set null');
            $table->text('message')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('creator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_requests');
    }
};
