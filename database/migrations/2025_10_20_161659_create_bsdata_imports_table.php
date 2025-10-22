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
        Schema::create('bsdata_imports', function (Blueprint $table) {
            $table->id();
            $table->string('version')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('factions_count')->default(0);
            $table->integer('units_count')->default(0);
            $table->json('errors')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bsdata_imports');
    }
};
