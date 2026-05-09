<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('portfolio_albums', 'cover_image_path')) {
            Schema::table('portfolio_albums', function (Blueprint $table) {
                $table->string('cover_image_path')->nullable()->after('cover_image_id');
            });
        }

        if (Schema::hasTable('photographer_galleries') && Schema::hasTable('gallery_image')) {
            $albumCount = DB::table('portfolio_albums')->count();
            $photographerGalleryCount = DB::table('photographer_galleries')->count();

            if ($albumCount === 0 && $photographerGalleryCount > 0) {
                DB::statement("
                    INSERT INTO portfolio_albums (
                        id,
                        user_id,
                        name,
                        description,
                        cover_image_id,
                        cover_image_path,
                        contains_nudity,
                        is_public,
                        visibility,
                        status,
                        custom_visibility_users,
                        display_order,
                        created_at,
                        updated_at
                    )
                    SELECT
                        g.id,
                        g.photographer_id,
                        g.title,
                        g.description,
                        NULL AS cover_image_id,
                        g.cover_image_path,
                        g.contains_nudity,
                        g.is_public,
                        COALESCE(g.visibility, 'public'),
                        COALESCE(g.status, 'draft'),
                        g.custom_visibility_users,
                        g.display_order,
                        g.created_at,
                        g.updated_at
                    FROM photographer_galleries g
                ");

                DB::statement("
                    UPDATE photographer_portfolio_images p
                    INNER JOIN gallery_image gi ON gi.image_id = p.id
                    SET
                        p.album_id = gi.gallery_id,
                        p.display_order = gi.display_order
                ");
            }

            DB::statement('DROP TABLE IF EXISTS gallery_image');
            DB::statement('DROP TABLE IF EXISTS photographer_galleries');
        }

        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'photographer_portfolio_images'
              AND COLUMN_NAME = 'album_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        Schema::table('photographer_portfolio_images', function (Blueprint $table) use ($foreignKeys) {
            foreach ($foreignKeys as $key) {
                $table->dropForeign($key->CONSTRAINT_NAME);
            }

            $table->foreign('album_id')
                ->references('id')
                ->on('portfolio_albums')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('photographer_portfolio_images', function (Blueprint $table) {
            $table->dropForeign(['album_id']);
        });

        if (Schema::hasColumn('portfolio_albums', 'cover_image_path')) {
            Schema::table('portfolio_albums', function (Blueprint $table) {
                $table->dropColumn('cover_image_path');
            });
        }

        Schema::create('photographer_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photographer_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public')->default(true);
            $table->boolean('contains_nudity')->default(false);
            $table->enum('visibility', ['public', 'link_only', 'hidden', 'custom'])->default('public');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->json('custom_visibility_users')->nullable();
            $table->timestamps();
        });

        Schema::create('gallery_image', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('photographer_galleries')->onDelete('cascade');
            $table->foreignId('image_id')->constrained('photographer_portfolio_images')->onDelete('cascade');
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->unique(['gallery_id', 'image_id']);
            $table->index(['gallery_id', 'display_order']);
        });
    }
};
