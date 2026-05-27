<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32)->default('post');
            $table->text('body')->nullable();
            $table->text('display_body')->nullable();
            $table->string('link_url')->nullable();
            $table->string('link_title')->nullable();
            $table->text('link_description')->nullable();
            $table->string('link_image')->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('visibility', 32)->default('connections');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['related_type', 'related_id']);
        });

        Schema::create('feed_post_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_post_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('feed_post_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentioned_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentioned_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 32)->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['feed_post_id', 'mentioned_user_id']);
            $table->index(['mentioned_user_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_post_mentions');
        Schema::dropIfExists('feed_post_images');
        Schema::dropIfExists('feed_posts');
    }
};
