<?php
namespace App\Http\Controllers;
use App\Support\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class ProjectExtrasController extends Controller {
    public function note(Request $r,int $project){$p=ProjectAccess::find($project);$d=$r->validate(['title'=>['required','string','max:180'],'content'=>['nullable','string','max:15000']]);$p->notes()->create($d+['user_id'=>$r->user()->id]);return back()->with('success','Catatan berhasil disimpan.');}
    public function milestone(Request $r,int $project){$p=ProjectAccess::find($project);$p->milestones()->create($r->validate(['title'=>['required','string','max:180'],'description'=>['nullable','string','max:2000'],'due_date'=>['nullable','date']]));return back()->with('success','Tahapan berhasil dibuat.');}
    public function backlog(Request $r,int $project){$p=ProjectAccess::find($project);$p->backlogItems()->create($r->validate(['title'=>['required','string','max:180'],'description'=>['nullable','string','max:3000'],'bucket'=>['required',Rule::in(['future','research','someday'])]])+['source'=>'manual']);return back()->with('success','Ide berhasil disimpan untuk nanti.');}
    public function baseline(Request $r,int $project){$p=ProjectAccess::find($project);$d=$r->validate(['in_scope'=>['nullable','string','max:15000'],'out_of_scope'=>['nullable','string','max:15000']]);$p->baseline()->updateOrCreate(['project_id'=>$p->id],$d);return back()->with('success','Batas pekerjaan awal berhasil disimpan.');}
}
