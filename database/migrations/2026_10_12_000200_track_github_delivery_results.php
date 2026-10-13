<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('github_deliveries', function (Blueprint $table) {
            $table->unsignedInteger('pending_jobs')->default(0);
            $table->unsignedInteger('succeeded_jobs')->default(0);
            $table->unsignedInteger('failed_jobs')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('github_deliveries', function (Blueprint $table) {
            $table->dropColumn(['pending_jobs', 'succeeded_jobs', 'failed_jobs']);
        });
    }
};
