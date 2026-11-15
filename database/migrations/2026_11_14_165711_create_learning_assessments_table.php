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
        if (Schema::hasTable('learning_assessments')) {
            return;
        }

        Schema::create('learning_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('learning_lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('maximum_score', 8, 2)->nullable();
            $table->text('evidence')->nullable();
            $table->date('assessed_on');
            $table->timestamps();
            $table->index(['owner_id', 'assessed_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_assessments');
    }
};
