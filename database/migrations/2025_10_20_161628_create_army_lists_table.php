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
        Schema::create('army_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tournament_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('faction_id')->nullable()->constrained()->onDelete('set null');
            $table->string('detachment')->nullable();
            $table->integer('points')->nullable();
            $table->string('pdf_path');
            $table->bigInteger('pdf_size')->nullable();
            $table->string('pdf_hash', 64)->nullable();
            $table->enum('status', ['draft', 'pending', 'validated', 'rejected'])->default('draft');
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'tournament_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('army_lists');
    }
};
