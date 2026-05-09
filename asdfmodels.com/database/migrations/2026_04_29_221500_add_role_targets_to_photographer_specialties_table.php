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
        Schema::table('photographer_specialties', function (Blueprint $table) {
            $table->boolean('applies_to_photographers')->default(true)->after('is_active');
            $table->boolean('applies_to_models')->default(false)->after('applies_to_photographers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photographer_specialties', function (Blueprint $table) {
            $table->dropColumn(['applies_to_photographers', 'applies_to_models']);
        });
    }
};
