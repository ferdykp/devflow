<?php

namespace Database\Seeders;

use App\Models\DocumentTaskImport;
use Illuminate\Database\Seeder;

class DocumentTaskImportSeeder extends Seeder
{
    public function run(): void
    {
        DocumentTaskImport::factory()->count(3)->create();
    }
}
