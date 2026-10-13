<?php
namespace App\Http\Controllers;
use App\Models\Workspace;
use Illuminate\Http\Request;
class WorkspaceController extends Controller {
    public function store(Request $request) {
        $data=$request->validate(['name'=>['required','string','max:100']]);
        $request->user()->workspaces()->create($data);
        return redirect()->route('projects.index')->with('success','Ruang kerja berhasil dibuat.');
    }
}
