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
        Schema::create('model_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            
            // Basic Information
            $table->text('bio')->nullable();
            $table->string('location_city')->nullable();
            $table->string('location_country')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            
            // Physical Stats - Male
            $table->string('height')->nullable(); // e.g., "6'0" or "183cm"
            $table->string('weight')->nullable(); // e.g., "75kg" or "165lbs"
            $table->string('chest')->nullable(); // Male
            $table->string('waist')->nullable();
            $table->string('inseam')->nullable(); // Male
            $table->string('shoe_size')->nullable();
            $table->string('suit_size')->nullable(); // Male
            $table->string('hair_color')->nullable();
            $table->string('eye_color')->nullable();
            
            // Physical Stats - Female
            $table->string('bust')->nullable(); // Female
            $table->string('hips')->nullable(); // Female
            $table->string('dress_size')->nullable(); // Female
            
            // Professional Information
            $table->string('experience_level')->nullable(); // e.g., "beginner", "intermediate", "professional"
            $table->json('specialties')->nullable(); // Array of specialties like ["fashion", "commercial", "beauty"]
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Contact Information
            $table->string('public_email')->nullable();
            $table->string('instagram')->nullable();
            $table->string('portfolio_website')->nullable();
            
            // Media
            $table->string('profile_photo_path')->nullable();
            $table->string('cover_photo_path')->nullable();
            
            // Settings
            $table->boolean('is_public')->default(true);
            $table->boolean('contains_nudity')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_profiles');
    }
};

