<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_check_ins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('routine_id')->constrained()->cascadeOnDelete();
            $table->date('occurred_on');
            $table->string('status');
            $table->string('activity_level')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['routine_id', 'occurred_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_check_ins');
    }
};
