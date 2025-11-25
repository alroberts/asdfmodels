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
        Schema::create('portfolio_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('photographer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('album_id')->nullable(); // Will add foreign key after table creation
            
            // File Paths
            $table->string('original_path')->nullable();
            $table->string('thumbnail_path');
            $table->string('medium_path');
            $table->string('full_path');
            
            // Metadata
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // e.g., "fashion", "beauty", "commercial"
            $table->json('tags')->nullable(); // Array of tags
            
            // Flags
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_polaroid')->default(false);
            $table->boolean('contains_nudity')->default(false);
            $table->boolean('is_public')->default(true);
            
            // Organization
            $table->integer('display_order')->default(0);
            $table->date('shot_date')->nullable();
            
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
            
            // Indexes
            $table->index(['model_id', 'is_polaroid']);
            $table->index(['model_id', 'is_featured']);
            $table->index(['model_id', 'is_public']);
        });
        
        // Note: Foreign key for album_id will be added in a separate migration
        // after portfolio_albums table is confirmed to exist
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolio_images', function (Blueprint $table) {
            $table->dropForeign(['album_id']);
        });
        
        Schema::dropIfExists('portfolio_images');
    }
};

