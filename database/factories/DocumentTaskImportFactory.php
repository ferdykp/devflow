<?php

namespace Database\Factories;

use App\Models\DocumentTaskImport;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DocumentTaskImport> */
class DocumentTaskImportFactory extends Factory
{
    public function definition(): array
    {
        return ['project_id' => Project::factory(), 'user_id' => fn (array $attributes) => Project::findOrFail($attributes['project_id'])->workspace->user_id, 'source_hash' => hash('sha256', fake()->uuid()), 'draft_tasks' => [['title' => 'Buat halaman masuk', 'description' => 'Validasi email dan kata sandi.', 'source_text' => '- Buat halaman masuk']]];
    }
}
