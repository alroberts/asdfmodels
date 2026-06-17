<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->timestamp('email_notification_sent_at')->nullable()->after('read_at');
            $table->timestamp('unsent_at')->nullable()->after('email_notification_sent_at');
            $table->foreignId('unsent_by_user_id')->nullable()->after('unsent_at')->constrained('users')->nullOnDelete();

            $table->index(['is_read', 'email_notification_sent_at', 'created_at'], 'messages_unread_email_index');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_unread_email_index');
            $table->dropConstrainedForeignId('unsent_by_user_id');
            $table->dropColumn(['email_notification_sent_at', 'unsent_at']);
        });
    }
};
