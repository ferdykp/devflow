<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo data is opt-in. Keep the default seeder safe for existing databases.
        if (filter_var(env('DEVFLOW_SEED_DEMO', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->call(DevFlowDemoSeeder::class);
        }
    }
}
