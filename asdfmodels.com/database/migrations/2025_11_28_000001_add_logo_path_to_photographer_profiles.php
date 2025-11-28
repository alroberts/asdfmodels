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
        Schema::table('photographer_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('photographer_profiles', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('cover_photo_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photographer_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('photographer_profiles', 'logo_path')) {
                $table->dropColumn('logo_path');
            }
        });
    }
};

