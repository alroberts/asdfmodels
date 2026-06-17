<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_albums', function (Blueprint $table) {
            $table->enum('visibility', ['public', 'link_only', 'hidden', 'custom'])->default('public')->after('contains_nudity');
            $table->enum('status', ['draft', 'published'])->default('draft')->after('visibility');
            $table->json('custom_visibility_users')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_albums', function (Blueprint $table) {
            $table->dropColumn(['visibility', 'status', 'custom_visibility_users']);
        });
    }
};
