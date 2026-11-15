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
        if (Schema::hasTable('calendar_events')) {
            return;
        }

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('routine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('learning_lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('personal_goal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->date('event_date')->nullable();
            $table->string('timezone', 64);
            $table->boolean('all_day')->default(false);
            $table->boolean('is_flexible')->default(false);
            $table->string('status', 20)->default('planned');
            $table->json('recurrence_rule')->nullable();
            $table->string('recurrence_series_id', 36)->nullable();
            $table->timestamps();
            $table->index(['owner_id', 'starts_at', 'ends_at']);
            $table->index(['owner_id', 'event_date']);
            $table->index('recurrence_series_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
