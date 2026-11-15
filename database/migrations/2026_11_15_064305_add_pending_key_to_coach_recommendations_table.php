<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('coach_recommendations', function (Blueprint $table): void {
            $table->dropUnique('coach_owner_kind_status_uniq');
            $table->string('pending_key', 50)->nullable()->after('status');
        });

        DB::table('coach_recommendations')
            ->where('status', 'pending')
            ->update(['pending_key' => DB::raw('kind')]);

        Schema::table('coach_recommendations', function (Blueprint $table): void {
            $table->unique(['owner_id', 'pending_key'], 'coach_owner_pending_key_uniq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasDuplicateStatuses = DB::table('coach_recommendations')
            ->select('owner_id', 'kind', 'status')
            ->groupBy('owner_id', 'kind', 'status')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateStatuses) {
            throw new RuntimeException('Cannot restore the previous coach recommendation constraint while duplicate status history exists.');
        }

        Schema::table('coach_recommendations', function (Blueprint $table): void {
            $table->dropUnique('coach_owner_pending_key_uniq');
            $table->dropColumn('pending_key');
            $table->unique(['owner_id', 'kind', 'status'], 'coach_owner_kind_status_uniq');
        });
    }
};
