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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('url');
            $table->integer('order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('menus')->onDelete('cascade');
            $table->enum('target', ['_self', '_blank'])->default('_self');
            $table->boolean('published')->default(true);
            $table->timestamps();
            
            $table->index('order');
            $table->index('published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
