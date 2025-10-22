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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('format', ['elimination', 'swiss', 'league'])->default('elimination');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->datetime('registration_deadline')->nullable();
            $table->integer('max_players')->nullable();
            $table->enum('status', ['draft', 'open', 'registration_closed', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->timestamp('bracket_generated_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('status');
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
