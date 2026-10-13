<?php
namespace App\Http\Controllers;
use App\Support\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
class GithubController extends Controller {
 public function link(Request $r,int $project){$p=ProjectAccess::find($project);$d=$r->validate(['full_name'=>['required','regex:/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/','max:200']]);$response=Http::acceptJson()->timeout(12)->get('https://api.github.com/repos/'.$d['full_name']);if(!$response->successful())throw ValidationException::withMessages(['full_name'=>'Dalam mode ini hanya repositori publik yang dapat diakses yang bisa dihubungkan.']);$data=$response->json();abort_unless(($data['private']??true)===false,422);$p->repositories()->updateOrCreate(['full_name'=>$data['full_name']],['github_id'=>$data['id'],'default_branch'=>$data['default_branch']??'main']);return back()->with('success','Repositori publik berhasil dihubungkan.');}
 public function sync(int $project, int $repository)
 {
     $p = ProjectAccess::find($project);
     $repo = $p->repositories()->findOrFail($repository);
     \App\Jobs\SyncGitHubRepository::dispatch($repo->id);
     return back()->with('success', 'Sinkronisasi repositori dijadwalkan. Aktivitas akan segera diperbarui.');
 }
}
