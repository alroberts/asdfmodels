<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('location_geoname_id')->nullable()->after('location_country');
            $table->string('location_country_code', 2)->nullable()->after('location_geoname_id');
            
            $table->index('location_geoname_id');
            $table->index('location_country_code');
        });
    }

    public function down(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->dropIndex(['location_geoname_id']);
            $table->dropIndex(['location_country_code']);
            $table->dropColumn(['location_geoname_id', 'location_country_code']);
        });
    }
};

