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
        Schema::create('gallery_image', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('photographer_galleries')->onDelete('cascade');
            $table->foreignId('image_id')->constrained('photographer_portfolio_images')->onDelete('cascade');
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->unique(['gallery_id', 'image_id']);
            $table->index(['gallery_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_image');
    }
};

