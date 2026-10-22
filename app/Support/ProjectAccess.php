<?php

namespace App\Support;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectAccess
{
    public static function find(int $id): Project
    {
        return Project::query()->whereHas('workspace', fn ($q) => $q->accessibleTo(Auth::id()))->findOrFail($id);
    }
}
