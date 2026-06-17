<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('username_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('username', 80)->unique();
            $table->string('type', 20)->default('original');
            $table->string('redirects_to_username', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });

        DB::table('users')
            ->whereNotNull('username')
            ->select(['id', 'username'])
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    DB::table('username_histories')->insert([
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'type' => 'original',
                        'redirects_to_username' => $user->username,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('username_histories');
    }
};
