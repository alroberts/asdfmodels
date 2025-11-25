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
        Schema::create('portfolio_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Basic Information
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('cover_image_id')->nullable(); // Will add foreign key constraint later
            
            // Settings
            $table->boolean('contains_nudity')->default(false);
            $table->boolean('is_public')->default(true);
            
            // Organization
            $table->integer('display_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_albums');
    }
};

