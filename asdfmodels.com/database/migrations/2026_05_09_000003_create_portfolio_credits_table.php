<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_credits', function (Blueprint $table) {
            $table->id();
            $table->string('creditable_type', 120);
            $table->unsignedBigInteger('creditable_id');
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('credited_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('credited_role', ['model', 'photographer'])->default('model');
            $table->enum('status', ['pending', 'accepted_visible', 'accepted_hidden', 'rejected'])->default('pending');
            $table->enum('source', ['owner_tag', 'tag_request'])->default('owner_tag');
            $table->text('note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['credited_user_id', 'credited_role', 'status']);
            $table->index(['owner_user_id', 'status']);
            $table->index(['creditable_type', 'creditable_id']);
            $table->unique(
                ['creditable_type', 'creditable_id', 'credited_user_id', 'credited_role'],
                'portfolio_credits_unique_credit'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_credits');
    }
};
