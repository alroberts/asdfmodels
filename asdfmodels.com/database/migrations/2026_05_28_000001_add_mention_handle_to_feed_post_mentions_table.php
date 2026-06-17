<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feed_post_mentions', function (Blueprint $table) {
            $table->string('mention_handle')->nullable()->after('mentioned_by_user_id');
        });

        DB::table('feed_post_mentions')
            ->join('users', 'users.id', '=', 'feed_post_mentions.mentioned_user_id')
            ->whereNull('feed_post_mentions.mention_handle')
            ->update(['feed_post_mentions.mention_handle' => DB::raw('users.username')]);
    }

    public function down(): void
    {
        Schema::table('feed_post_mentions', function (Blueprint $table) {
            $table->dropColumn('mention_handle');
        });
    }
};
