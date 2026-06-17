<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photographer_profiles', function (Blueprint $table) {
            $table->boolean('show_company_on_profile')->default(false)->after('display_name_format');
        });
    }

    public function down(): void
    {
        Schema::table('photographer_profiles', function (Blueprint $table) {
            $table->dropColumn('show_company_on_profile');
        });
    }
};
