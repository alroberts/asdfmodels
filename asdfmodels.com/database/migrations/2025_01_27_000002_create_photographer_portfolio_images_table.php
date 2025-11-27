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
        Schema::create('photographer_portfolio_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photographer_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('album_id')->nullable();
            
            // File Paths
            $table->string('original_path')->nullable();
            $table->string('thumbnail_path');
            $table->string('medium_path');
            $table->string('full_path');
            
            // Metadata
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // e.g., "fashion", "portrait", "commercial", "wedding"
            $table->json('tags')->nullable(); // Array of tags
            $table->foreignId('model_id')->nullable()->constrained('users')->onDelete('set null'); // Model in the photo (if applicable)
            
            // Flags
            $table->boolean('is_featured')->default(false);
            $table->boolean('contains_nudity')->default(false);
            $table->boolean('is_public')->default(true);
            
            // Organization
            $table->integer('display_order')->default(0);
            $table->date('shot_date')->nullable();
            
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
            
            // Indexes
            $table->index(['photographer_id', 'is_featured']);
            $table->index(['photographer_id', 'is_public']);
            $table->index(['photographer_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photographer_portfolio_images');
    }
};

