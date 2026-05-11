<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_verifications', function (Blueprint $table) {
            $table->string('liveness_video_path')->nullable()->after('video_path');
            $table->string('liveness_code', 20)->nullable()->after('liveness_video_path');
            $table->string('capture_method', 30)->nullable()->after('liveness_code');
        });
    }

    public function down(): void
    {
        Schema::table('model_verifications', function (Blueprint $table) {
            $table->dropColumn(['liveness_video_path', 'liveness_code', 'capture_method']);
        });
    }
};
