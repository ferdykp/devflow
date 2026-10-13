<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('github_repositories', function (Blueprint $table) {
            $table->unsignedBigInteger('installation_id')->nullable()->index();
        });
    }
    public function down(): void
    {
        Schema::table('github_repositories', fn (Blueprint $table) => $table->dropColumn('installation_id'));
    }
};
