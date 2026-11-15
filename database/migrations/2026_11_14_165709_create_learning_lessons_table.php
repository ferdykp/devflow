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
        if (Schema::hasTable('learning_lessons')) {
            return;
        }

        Schema::create('learning_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_module_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('objectives')->nullable();
            $table->longText('materials_markdown')->nullable();
            $table->string('reference_url', 2048)->nullable();
            $table->string('status', 40)->default('not_started');
            $table->text('competency_evidence')->nullable();
            $table->timestamp('status_updated_at')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->boolean('knowledge_gap')->default(false);
            $table->timestamps();
            $table->index(['learning_module_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_lessons');
    }
};
