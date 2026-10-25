<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function create(): View
    {
        return view('projects.workspace-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $workspace = $request->user()->workspaces()->create($data);

        return redirect()->route('projects.create', ['workspace' => $workspace->id])
            ->with('success', 'Ruang kerja berhasil dibuat. Tambahkan proyek pertamamu.');
    }
}
