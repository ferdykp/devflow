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
        if (Schema::hasTable('learning_sessions')) {
            return;
        }

        Schema::create('learning_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('learning_path_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_type', 20)->default('study');
            $table->unsignedSmallInteger('duration_minutes');
            $table->date('occurred_on');
            $table->text('evidence')->nullable();
            $table->timestamps();
            $table->index(['owner_id', 'occurred_on']);
            $table->index(['learning_path_id', 'occurred_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_sessions');
    }
};
