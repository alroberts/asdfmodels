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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('two_factor_enabled')->default(false)->after('is_photographer');
            $table->string('two_factor_method')->nullable()->after('two_factor_enabled'); // 'authenticator' or 'email'
            $table->text('two_factor_secret')->nullable()->after('two_factor_method'); // Encrypted secret for authenticator
            $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            $table->string('two_factor_email_code')->nullable()->after('two_factor_confirmed_at'); // Temporary email OTP
            $table->timestamp('two_factor_email_code_expires_at')->nullable()->after('two_factor_email_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_method',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'two_factor_email_code',
                'two_factor_email_code_expires_at',
            ]);
        });
    }
};

