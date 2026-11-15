<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            if (! Schema::hasColumn('tasks', 'owner_id')) {
                $table->foreignId('owner_id')->nullable()->constrained('users')->cascadeOnDelete();
            }
        });

        Schema::table('tasks', function (Blueprint $table): void {
            $table->unsignedBigInteger('project_id')->nullable()->change();
        });

        Schema::table('tasks', function (Blueprint $table): void {
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        if (DB::table('tasks')->whereNull('project_id')->exists()) {
            throw new RuntimeException('Personal tasks still exist. Export or remove them before rolling back ownership.');
        }

        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropIndex(['owner_id', 'status']);
            $table->dropConstrainedForeignId('owner_id');
            $table->unsignedBigInteger('project_id')->nullable(false)->change();
        });
    }
};
