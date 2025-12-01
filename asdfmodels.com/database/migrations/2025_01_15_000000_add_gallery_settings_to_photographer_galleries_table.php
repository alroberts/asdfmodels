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
        Schema::table('photographer_galleries', function (Blueprint $table) {
            $table->boolean('contains_nudity')->default(false)->after('is_public');
            $table->enum('visibility', ['public', 'link_only', 'hidden', 'custom'])->default('public')->after('contains_nudity');
            $table->enum('status', ['draft', 'published'])->default('draft')->after('visibility');
            $table->json('custom_visibility_users')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photographer_galleries', function (Blueprint $table) {
            $table->dropColumn(['contains_nudity', 'visibility', 'status', 'custom_visibility_users']);
        });
    }
};


