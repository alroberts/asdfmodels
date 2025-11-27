<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add gender and experience_start_year to model_profiles
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->integer('experience_start_year')->nullable()->after('experience_level');
        });

        // Add gender, professional_name, and experience_start_year to photographer_profiles
        Schema::table('photographer_profiles', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('bio');
            $table->string('professional_name')->nullable()->after('gender');
            $table->integer('experience_start_year')->nullable()->after('experience_level');
        });
    }

    public function down(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->dropColumn('experience_start_year');
        });

        Schema::table('photographer_profiles', function (Blueprint $table) {
            $table->dropColumn(['gender', 'professional_name', 'experience_start_year']);
        });
    }
};

