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
        Schema::create('photographer_image_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_image_id')->constrained('portfolio_images')->onDelete('cascade');
            $table->foreignId('photographer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('model_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['photographer', 'model'])->comment('Who tagged whom: photographer tagged model, or model linked photographer');
            $table->timestamps();
            
            // Indexes
            $table->index(['portfolio_image_id', 'photographer_id']);
            $table->index(['portfolio_image_id', 'model_id']);
            $table->unique(['portfolio_image_id', 'photographer_id', 'model_id', 'role'], 'unique_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photographer_image_tags');
    }
};

