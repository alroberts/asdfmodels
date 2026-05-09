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
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->string('measurement_system')->default('metric')->after('gender');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('measurement_system');
            $table->decimal('weight_kg', 6, 2)->nullable()->after('height_cm');
            $table->decimal('chest_cm', 6, 2)->nullable()->after('weight_kg');
            $table->decimal('waist_cm', 6, 2)->nullable()->after('chest_cm');
            $table->decimal('inseam_cm', 6, 2)->nullable()->after('waist_cm');
            $table->decimal('bust_cm', 6, 2)->nullable()->after('inseam_cm');
            $table->decimal('hips_cm', 6, 2)->nullable()->after('bust_cm');
            $table->string('shoe_size_region')->nullable()->after('hips_cm');
            $table->string('shoe_size_value')->nullable()->after('shoe_size_region');
            $table->string('dress_size_region')->nullable()->after('shoe_size_value');
            $table->string('dress_size_value')->nullable()->after('dress_size_region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'measurement_system',
                'height_cm',
                'weight_kg',
                'chest_cm',
                'waist_cm',
                'inseam_cm',
                'bust_cm',
                'hips_cm',
                'shoe_size_region',
                'shoe_size_value',
                'dress_size_region',
                'dress_size_value',
            ]);
        });
    }
};
