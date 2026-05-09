<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('portfolio_image_comments')) {
            $this->addIndexIfMissing('portfolio_image_comments', 'pic_image_idx', ['imageable_type', 'imageable_id', 'created_at']);
            $this->addIndexIfMissing('portfolio_image_comments', 'pic_user_idx', ['user_id', 'created_at']);

            return;
        }

        Schema::create('portfolio_image_comments', function (Blueprint $table) {
            $table->id();
            $table->string('imageable_type', 120);
            $table->unsignedBigInteger('imageable_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index(['imageable_type', 'imageable_id', 'created_at'], 'pic_image_idx');
            $table->index(['user_id', 'created_at'], 'pic_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_image_comments');
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        try {
            DB::statement(sprintf(
                'CREATE INDEX %s ON %s (%s)',
                $index,
                $table,
                implode(', ', $columns)
            ));
        } catch (\Throwable $e) {
            // The failed first deploy may already have created one or both indexes.
        }
    }
};
