<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            if (! Schema::hasColumn('tasks', 'personal_goal_id')) {
                $table->foreignId('personal_goal_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('tasks', 'learning_lesson_id')) {
                $table->foreignId('learning_lesson_id')->nullable()->constrained()->nullOnDelete();
            }
        });

        Schema::table('routines', function (Blueprint $table): void {
            if (! Schema::hasColumn('routines', 'personal_goal_id')) {
                $table->foreignId('personal_goal_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('routines', 'learning_path_id')) {
                $table->foreignId('learning_path_id')->nullable()->constrained()->nullOnDelete();
            }
        });

        Schema::table('personal_notes', function (Blueprint $table): void {
            if (! Schema::hasColumn('personal_notes', 'learning_lesson_id')) {
                $table->foreignId('learning_lesson_id')->nullable()->constrained()->nullOnDelete();
            }
        });

        Schema::table('learning_sessions', function (Blueprint $table): void {
            if (! Schema::hasColumn('learning_sessions', 'calendar_event_id')) {
                $table->foreignId('calendar_event_id')->nullable()->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Cross-module links may contain user data; remove links in a reviewed forward migration.');
    }
};
