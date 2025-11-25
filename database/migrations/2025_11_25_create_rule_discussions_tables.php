<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catégories
        Schema::create('rule_discussion_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Discussions
        Schema::create('rule_discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('rule_discussion_categories')->onDelete('cascade');
            $table->string('title');
            $table->longText('description');
            $table->string('image_path')->nullable();
            $table->enum('status', ['open', 'closed', 'resolved'])->default('open');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Réponses
        Schema::create('rule_discussion_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discussion_id')->constrained('rule_discussions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->longText('content');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        // Paramètres
        Schema::create('rule_discussion_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('max_images_per_discussion')->default(1);
            $table->boolean('enable_notifications')->default(true);
            $table->boolean('enable_moderation')->default(true);
            $table->integer('auto_archive_days')->default(90);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rule_discussion_settings');
        Schema::dropIfExists('rule_discussion_replies');
        Schema::dropIfExists('rule_discussions');
        Schema::dropIfExists('rule_discussion_categories');
    }
};
