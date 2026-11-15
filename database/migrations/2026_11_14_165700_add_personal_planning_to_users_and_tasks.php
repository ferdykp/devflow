<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'timezone')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('timezone', 64)->nullable();
            });
        }

        Schema::table('tasks', function (Blueprint $table): void {
            if (! Schema::hasColumn('tasks', 'owner_id')) {
                $table->foreignId('owner_id')->nullable()->constrained('users')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('tasks', 'category')) {
                $table->string('category', 100)->nullable();
            }

            if (! Schema::hasColumn('tasks', 'tags')) {
                $table->json('tags')->nullable();
            }

            if (! Schema::hasColumn('tasks', 'recurrence_rule')) {
                $table->json('recurrence_rule')->nullable();
            }

            if (! Schema::hasColumn('tasks', 'recurrence_series_id')) {
                $table->uuid('recurrence_series_id')->nullable();
            }

            if (! Schema::hasColumn('tasks', 'recurrence_index')) {
                $table->unsignedInteger('recurrence_index')->nullable();
            }

            if (! Schema::hasColumn('tasks', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable();
            }

            if (! Schema::hasColumn('tasks', 'estimated_minutes')) {
                $table->unsignedSmallInteger('estimated_minutes')->nullable();
            }
        });

        Schema::table('tasks', function (Blueprint $table): void {
            $table->unsignedBigInteger('project_id')->nullable()->change();
        });

        if (! Schema::hasIndex('tasks', 'task_rec_idx_uniq')) {
            Schema::table('tasks', function (Blueprint $table): void {
                $table->unique(['recurrence_series_id', 'recurrence_index'], 'task_rec_idx_uniq');
            });
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Personal tasks may contain user data; reverse this migration only after a separately reviewed export and cleanup.');
    }
};
