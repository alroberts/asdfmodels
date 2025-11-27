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
        Schema::create('photographer_specialties', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'fashion', 'portrait'
            $table->string('label'); // e.g., 'Fashion', 'Portrait'
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photographer_specialties');
    }
};

