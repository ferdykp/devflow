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
        if (Schema::hasTable('personal_notes')) {
            return;
        }

        Schema::create('personal_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('personal_goal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('collection', 100)->nullable();
            $table->string('title', 200);
            $table->longText('content')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->index(['owner_id', 'archived_at', 'is_pinned']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_notes');
    }
};
