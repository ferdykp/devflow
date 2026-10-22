<?php

namespace Database\Seeders;

use App\Models\WorkspaceInvitation;
use Illuminate\Database\Seeder;

class WorkspaceInvitationSeeder extends Seeder
{
    public function run(): void
    {
        WorkspaceInvitation::factory()->count(3)->create();
    }
}
