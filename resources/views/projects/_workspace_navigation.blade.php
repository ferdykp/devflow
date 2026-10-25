@php
    $sections = [
        'overview' => ['Ringkasan', 'fa-house'], 'tasks' => ['Tugas', 'fa-list-check'],
        'prd' => ['Dokumen', 'fa-file-lines'], 'resume' => ['Lanjutkan', 'fa-clock-rotate-left'],
        'activity' => ['Aktivitas', 'fa-code-commit'], 'notes' => ['Catatan', 'fa-note-sticky'],
        'backlog' => ['Ide nanti', 'fa-lightbulb'], 'milestones' => ['Tahapan', 'fa-flag'],
        'scope' => ['Cakupan', 'fa-shield-halved'], 'github' => ['GitHub', 'fa-code-branch'],
        'settings' => ['Pengaturan', 'fa-gear'],
    ];
    $section = isset($section) && is_string($section) && array_key_exists($section, $sections) ? $section : 'overview';
    $advanced = ['notes', 'activity', 'milestones', 'scope', 'backlog', 'github', 'settings'];
@endphp
<header class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
    <div class="min-w-0">
        <p class="mb-2 flex flex-wrap items-center gap-2 text-xs text-zinc-500"><a href="{{ route('projects.index') }}" class="hover:text-blue-700">Proyek</a><span aria-hidden="true">/</span><span class="break-words">{{ $project->workspace->name }}</span></p>
        <div class="flex flex-wrap items-center gap-3"><h1 class="min-w-0 max-w-full [overflow-wrap:anywhere] text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">{{ $project->name }}</h1><x-ui.status :value="$project->status" /></div>
        <p class="mt-2 max-w-2xl break-words text-sm leading-6 text-zinc-500">{{ $project->description ?: 'Daftar pekerjaan sederhana untukmu dan tim.' }}</p>
    </div>
    <x-ui.button :href="route('projects.edit', $project)" variant="ghost"><i class="fa-solid fa-pen text-xs" aria-hidden="true"></i>Edit proyek</x-ui.button>
</header>
<div class="min-w-0">
    <nav class="mb-6 flex items-center gap-1 border-b border-zinc-200 pb-3 sm:flex-wrap" aria-label="Bagian proyek">
        @foreach(['tasks', 'prd', 'overview', 'resume'] as $key)
            <a href="{{ $key === 'resume' ? route('projects.resume', $project) : route('projects.show', ['project' => $project, 'section' => $key]) }}"
                class="{{ in_array($key, ['overview', 'resume'], true) ? 'hidden sm:inline-flex' : 'inline-flex' }} min-h-11 items-center gap-2 rounded-lg px-2 py-2.5 text-xs font-medium transition-colors duration-150 sm:px-3 sm:text-sm motion-reduce:transition-none {{ $section === $key ? 'bg-blue-50 text-blue-700' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}"
                @if($section === $key) aria-current="page" @endif><span class="hidden sm:inline"><i class="fa-solid {{ $sections[$key][1] }} w-4 text-center" aria-hidden="true"></i></span>{{ $sections[$key][0] }}</a>
        @endforeach
        <details data-project-menu class="relative ml-auto" @if(in_array($section, $advanced, true)) data-current="true" @endif>
            <summary aria-label="Menu proyek lainnya" class="flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-lg px-2 sm:px-3 py-2.5 text-sm font-medium transition-colors hover:bg-zinc-100 [&::-webkit-details-marker]:hidden {{ in_array($section, $advanced, true) ? 'bg-blue-50 text-blue-700' : 'text-zinc-600' }}">
                <i class="fa-solid fa-ellipsis w-4 text-center" aria-hidden="true"></i><span class="hidden sm:inline">{{ in_array($section, $advanced, true) ? $sections[$section][0] : 'Lainnya' }}</span><span class="hidden sm:inline"><i class="fa-solid fa-chevron-down text-[10px]" aria-hidden="true"></i></span>
            </summary>
            <div data-project-menu-panel class="absolute right-0 top-full z-50 mt-2 max-h-[40dvh] w-64 max-w-[calc(100vw-40px)] min-w-56 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-2 shadow-lg sm:w-64 sm:max-h-[60dvh]">
                <div class="border-b border-zinc-100 pb-2 sm:hidden">
                    <a href="{{ route('projects.show', ['project' => $project, 'section' => 'overview']) }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm text-zinc-600 hover:bg-zinc-100">Ringkasan</a>
                    <a href="{{ route('projects.resume', $project) }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm text-zinc-600 hover:bg-zinc-100">Lanjutkan</a>
                </div>
                <p class="px-3 py-2 text-xs font-medium text-zinc-500">Bagian proyek lainnya</p>
                @foreach($advanced as $key)
                    <a href="{{ route('projects.show', ['project' => $project, 'section' => $key]) }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ $section === $key ? 'bg-blue-50 text-blue-700' : 'text-zinc-600 hover:bg-zinc-100' }}" @if($section === $key) aria-current="page" @endif><span class="hidden sm:inline"><i class="fa-solid {{ $sections[$key][1] }} w-4 text-center" aria-hidden="true"></i></span>{{ $sections[$key][0] }}</a>
                @endforeach
                <a href="{{ route('workspaces.members.show', $project->workspace) }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100"><i class="fa-solid fa-users w-4 text-center" aria-hidden="true"></i>Anggota ruang kerja</a>
                <a href="{{ route('projects.insights', $project) }}" class="flex min-h-11 items-center gap-3 rounded-lg border-t border-zinc-100 px-3 py-2.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100"><i class="fa-solid fa-wand-magic-sparkles w-4 text-center" aria-hidden="true"></i>Analisis AI</a>
            </div>
        </details>
    </nav>
