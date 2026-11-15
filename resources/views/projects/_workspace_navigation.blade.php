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
<header class="df-page-heading df-panel mb-4 flex flex-col items-start justify-between gap-4 rounded-2xl border p-5 sm:flex-row sm:items-center sm:p-6">
    <div class="min-w-0">
        <p class="mb-2 flex flex-wrap items-center gap-2 text-xs text-[var(--df-muted)]"><a href="{{ route('projects.index') }}" class="hover:text-[var(--df-action)]">Proyek</a><span aria-hidden="true">/</span><span class="break-words">{{ $project->workspace->name }}</span></p>
        <div class="flex flex-wrap items-center gap-3"><h1 class="min-w-0 max-w-full font-display text-2xl font-semibold tracking-tight text-[var(--df-ink)] [overflow-wrap:anywhere] sm:text-3xl">{{ $project->name }}</h1><x-ui.status :value="$project->status" /></div>
        <p class="mt-2 max-w-2xl break-words text-sm leading-6 text-zinc-500">{{ $project->description ?: 'Daftar pekerjaan sederhana untukmu dan tim.' }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2"><a href="{{ route('projects.resume', $project) }}" class="df-button-secondary inline-flex min-h-11 items-center gap-2 rounded-xl px-3 text-sm font-semibold"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i><span>Lanjutkan</span></a><x-ui.button :href="route('projects.edit', $project)" variant="ghost"><i class="fa-solid fa-pen text-xs" aria-hidden="true"></i>Edit proyek</x-ui.button></div>
</header>
<div class="min-w-0">
    <nav class="df-panel mb-6 flex min-w-0 items-center gap-1 overflow-x-auto rounded-2xl border p-2" aria-label="Bagian proyek">
        @foreach(['overview', 'tasks', 'prd'] as $key)
            <a href="{{ route('projects.show', ['project' => $project, 'section' => $key]) }}"
                class="inline-flex min-h-11 shrink-0 items-center gap-2 whitespace-nowrap rounded-xl px-3 py-2.5 text-xs font-medium transition-colors duration-150 sm:text-sm motion-reduce:transition-none {{ $section === $key ? 'bg-[var(--df-action-soft)] text-[var(--df-action)]' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}"
                @if($section === $key) aria-current="page" @endif><i class="fa-solid {{ $sections[$key][1] }} w-4 text-center" aria-hidden="true"></i>{{ $sections[$key][0] }}</a>
        @endforeach
        <a href="{{ route('projects.resume', $project) }}" class="inline-flex min-h-11 shrink-0 items-center gap-2 whitespace-nowrap rounded-xl px-3 py-2.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-950"><i class="fa-solid {{ $sections['resume'][1] }} w-4 text-center" aria-hidden="true"></i>{{ $sections['resume'][0] }}</a>
        <details data-project-menu class="relative ml-auto" @if(in_array($section, $advanced, true)) data-current="true" @endif>
            <summary aria-label="Menu proyek lainnya{{ in_array($section, $advanced, true) ? ', '.$sections[$section][0].' dipilih' : '' }}" class="flex min-h-11 shrink-0 cursor-pointer list-none items-center gap-2 whitespace-nowrap rounded-xl px-3 py-2.5 text-sm font-medium transition-colors hover:bg-zinc-100 [&::-webkit-details-marker]:hidden {{ in_array($section, $advanced, true) ? 'bg-blue-50 text-blue-700' : 'text-zinc-600' }}">
                <i class="fa-solid fa-ellipsis w-4 text-center" aria-hidden="true"></i><span class="hidden sm:inline">{{ in_array($section, $advanced, true) ? $sections[$section][0] : 'Lainnya' }}</span><span class="hidden sm:inline"><i class="fa-solid fa-chevron-down text-[10px]" aria-hidden="true"></i></span>
            </summary>
            <div data-project-menu-panel class="absolute right-0 top-full z-50 mt-2 max-h-[40dvh] w-64 max-w-[calc(100vw-40px)] min-w-56 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-2 shadow-lg sm:w-64 sm:max-h-[60dvh]">
                <div class="border-b border-zinc-100 pb-2 sm:hidden">
                    <a href="{{ route('projects.show', ['project' => $project, 'section' => 'overview']) }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm text-zinc-600 hover:bg-zinc-100">Ringkasan</a>
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
