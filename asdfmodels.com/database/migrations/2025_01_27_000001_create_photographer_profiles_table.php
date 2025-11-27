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
        Schema::create('photographer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            
            // Basic Information
            $table->text('bio')->nullable();
            $table->string('location_city')->nullable();
            $table->string('location_country')->nullable();
            
            // Professional Information
            $table->string('experience_level')->nullable(); // e.g., "beginner", "intermediate", "professional"
            $table->json('specialties')->nullable(); // Array of specialties like ["fashion", "portrait", "commercial", "wedding"]
            $table->json('equipment')->nullable(); // Array of equipment/cameras
            $table->json('services_offered')->nullable(); // Array of services like ["headshots", "portraits", "events"]
            $table->string('studio_location')->nullable();
            $table->boolean('available_for_travel')->default(false);
            $table->text('pricing_info')->nullable();
            
            // Verification
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Contact Information
            $table->string('public_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('instagram')->nullable();
            $table->string('portfolio_website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            
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
        Schema::dropIfExists('photographer_profiles');
    }
};

