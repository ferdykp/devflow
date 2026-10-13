<?php
namespace App\Http\Controllers;
use App\Support\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PrdController extends Controller {
 public function store(Request $r,int $project){$p=ProjectAccess::find($project);$d=$r->validate(['title'=>['required','string','max:200'],'content_markdown'=>['required','string','max:200000']]);DB::transaction(function()use($p,$d){$doc=$p->documents()->create(['title'=>$d['title']]);$doc->versions()->create(['version_number'=>1,'content_markdown'=>$d['content_markdown']]);});return back()->with('success','Draf PRD berhasil dibuat.');}
 public function revise(Request $r,int $project,int $document){$p=ProjectAccess::find($project);$d=$r->validate(['content_markdown'=>['required','string','max:200000']]);DB::transaction(function()use($p,$document,$d){$doc=$p->documents()->lockForUpdate()->findOrFail($document);$last=(int)$doc->versions()->max('version_number');$doc->versions()->create(['version_number'=>$last+1,'content_markdown'=>$d['content_markdown']]);});return back()->with('success','Versi draf baru berhasil dibuat.');}
 public function approve(Request $r,int $project,int $document,int $version){$p=ProjectAccess::find($project);DB::transaction(function()use($p,$document,$version,$r){$doc=$p->documents()->lockForUpdate()->findOrFail($document);$v=$doc->versions()->lockForUpdate()->findOrFail($version);abort_unless($v->status==='draft',409,'Hanya draf yang bisa disetujui.');$doc->versions()->where('status','approved')->update(['status'=>'superseded']);$v->update(['status'=>'approved','approved_by'=>$r->user()->id,'approved_at'=>now(),'content_hash'=>hash('sha256',$v->content_markdown)]);});return back()->with('success','Dokumen PRD disetujui sebagai acuan.');}
}
