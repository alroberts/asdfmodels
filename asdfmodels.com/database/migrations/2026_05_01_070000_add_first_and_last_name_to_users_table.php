<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
        });

        User::query()
            ->select('id', 'name')
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    $name = trim((string) $user->name);

                    if ($name === '') {
                        continue;
                    }

                    $parts = preg_split('/\s+/', $name) ?: [];
                    $firstName = trim((string) array_shift($parts));
                    $lastName = trim(implode(' ', $parts));

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'first_name' => $firstName !== '' ? $firstName : null,
                            'last_name' => $lastName !== '' ? $lastName : null,
                            'name' => $firstName !== '' ? $firstName : $name,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
