<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('connections', function (Blueprint $table) {
            $table->foreignId('blocked_by_user_id')->nullable()->after('message')->constrained('users')->nullOnDelete();
            $table->timestamp('blocked_at')->nullable()->after('blocked_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('connections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('blocked_by_user_id');
            $table->dropColumn('blocked_at');
        });
    }
};
