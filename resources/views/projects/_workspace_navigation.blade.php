@php
 $sections = [
  'overview'=>['Ringkasan','fa-house'], 'tasks'=>['Tugas','fa-list-check'], 'resume'=>['Lanjutkan','fa-clock-rotate-left'],
  'activity'=>['Aktivitas','fa-code-commit'], 'notes'=>['Catatan','fa-note-sticky'], 'backlog'=>['Ide nanti','fa-lightbulb'],
  'milestones'=>['Tahapan','fa-flag'], 'scope'=>['Cakupan','fa-shield-halved'], 'prd'=>['Dokumen','fa-file-lines'],
  'github'=>['GitHub','fa-code-branch'], 'settings'=>['Pengaturan','fa-gear']
 ];
 $section = isset($section) && is_string($section) && array_key_exists($section, $sections) ? $section : 'overview';
@endphp
<header class="mb-7 flex flex-col items-start justify-between gap-5 sm:flex-row sm:items-center">
  <div class="min-w-0">
    <p class="mb-2 text-[10px] font-bold uppercase tracking-[.15em] text-slate-500"><a href="{{ route('projects.index') }}" class="hover:text-blue-600">Proyek</a> / {{ $project->workspace->name }}</p>
    <div class="flex flex-wrap items-center gap-3"><h1 class="text-3xl font-semibold tracking-tight text-slate-950">{{ $project->name }}</h1><span class="rounded-md border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-600">{{ \App\Support\UiLabel::status($project->status) }}</span></div>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ $project->description ?: 'Ruang kerja pengembanganmu' }}</p>
  </div>
  <a href="{{ route('projects.resume',$project) }}" class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-slate-950 px-4 py-3 text-xs font-semibold text-white transition hover:bg-slate-800"><i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>Lanjutkan pekerjaan</a>
</header>
<div class="min-w-0">
  <nav class="mb-8 flex min-w-0 items-center gap-1 overflow-x-auto border-b border-slate-200 pb-3 lg:overflow-visible" aria-label="Bagian proyek">
    @foreach(['overview','tasks','resume','activity','notes'] as $key)
      <a href="{{ $key === 'resume' ? route('projects.resume',$project) : route('projects.show',['project'=>$project,'section'=>$key]) }}" class="inline-flex min-h-10 shrink-0 items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2.5 text-xs font-semibold transition {{ $section === $key ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}" @if($section === $key) aria-current="page" @endif><i class="fa-solid {{ $sections[$key][1] }}" aria-hidden="true"></i>{{ $sections[$key][0] }}</a>
    @endforeach
    @php $advanced = ['backlog','milestones','scope','prd','github','settings']; @endphp
    <details class="relative ml-auto shrink-0" @if(in_array($section,$advanced,true)) data-current="true" @endif>
      <summary aria-label="Menu proyek lainnya" class="flex min-h-10 cursor-pointer list-none items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 [&::-webkit-details-marker]:hidden"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i>{{ in_array($section,$advanced,true) ? $sections[$section][0] : 'Lainnya' }}<i class="fa-solid fa-chevron-down text-[10px]" aria-hidden="true"></i></summary>
      <div class="fixed right-4 top-40 z-50 mt-2 w-60 lg:absolute lg:right-0 lg:top-full rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
        <p class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Rencana dan integrasi</p>
        @foreach($advanced as $key)
          <a href="{{ route('projects.show',['project'=>$project,'section'=>$key]) }}" class="flex min-h-10 items-center gap-3 rounded-lg px-3 py-2 text-xs font-semibold {{ $section===$key ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100' }}" @if($section===$key) aria-current="page" @endif><i class="fa-solid {{ $sections[$key][1] }} w-4 text-center" aria-hidden="true"></i>{{ $sections[$key][0] }}</a>
        @endforeach
        <a href="{{ route('projects.insights',$project) }}" class="flex min-h-10 items-center gap-3 rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-wand-magic-sparkles w-4 text-center" aria-hidden="true"></i>Analisis AI</a>
      </div>
    </details>
  </nav>
