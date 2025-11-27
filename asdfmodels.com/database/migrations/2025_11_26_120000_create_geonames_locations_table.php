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
        Schema::create('geonames_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('geoname_id')->primary();
            $table->string('name', 200);
            $table->string('ascii_name', 200)->nullable();
            $table->text('alternate_names')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->char('feature_class', 1)->nullable();
            $table->string('feature_code', 10)->nullable();
            $table->char('country_code', 2)->index();
            $table->string('admin1_code', 20)->nullable()->index();
            $table->string('admin2_code', 80)->nullable();
            $table->unsignedBigInteger('population')->default(0)->index();
            $table->string('timezone', 40)->nullable();
            $table->date('modification_date')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('ascii_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geonames_locations');
    }
};


