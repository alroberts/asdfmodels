<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 80)->nullable()->after('last_name');
        });

        DB::table('users')
            ->select(['id', 'first_name', 'last_name', 'name', 'email'])
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'username' => $this->makeUniqueUsername($user),
                        ]);
                }
            });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }

    private function makeUniqueUsername(object $user): string
    {
        $firstName = trim((string) $user->first_name);
        $lastName = trim((string) $user->last_name);

        if ($firstName === '' && filled($user->name)) {
            $parts = preg_split('/\s+/', trim((string) $user->name)) ?: [];
            $firstName = $parts[0] ?? '';
            $lastName = count($parts) > 1 ? $parts[count($parts) - 1] : $lastName;
        }

        if ($firstName === '') {
            $firstName = Str::before((string) $user->email, '@') ?: 'member';
        }

        $lastInitial = $lastName !== '' ? mb_substr($lastName, 0, 1) : '';
        $base = Str::slug(trim($firstName . ' ' . $lastInitial)) ?: 'member';
        $base = Str::limit($base, 66, '');

        do {
            $username = $base . '-' . random_int(1000, 9999);
        } while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists());

        return $username;
    }
};
