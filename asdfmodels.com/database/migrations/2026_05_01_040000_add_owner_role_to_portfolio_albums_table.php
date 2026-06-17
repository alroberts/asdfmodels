<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_albums', function (Blueprint $table) {
            $table->enum('owner_role', ['model', 'photographer'])->nullable()->after('user_id');
        });

        DB::statement("
            UPDATE portfolio_albums pa
            INNER JOIN users u ON u.id = pa.user_id
            SET pa.owner_role = CASE
                WHEN u.is_photographer = 1 THEN 'photographer'
                ELSE 'model'
            END
        ");
    }

    public function down(): void
    {
        Schema::table('portfolio_albums', function (Blueprint $table) {
            $table->dropColumn('owner_role');
        });
    }
};
