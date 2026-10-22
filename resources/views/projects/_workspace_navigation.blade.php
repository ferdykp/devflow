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
        <div class="flex flex-wrap items-center gap-3"><h1 class="break-words text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">{{ $project->name }}</h1><x-ui.status :value="$project->status" /></div>
        <p class="mt-2 max-w-2xl break-words text-sm leading-6 text-zinc-500">{{ $project->description ?: 'Tugas, dokumen, dan konteks proyek dalam satu tempat.' }}</p>
    </div>
    <x-ui.button :href="route('projects.resume', $project)" variant="secondary"><i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>Lanjutkan pekerjaan</x-ui.button>
</header>
<div class="min-w-0">
    <nav class="mb-7 grid grid-cols-2 gap-2 border-b border-zinc-200 pb-4 sm:flex sm:flex-wrap sm:items-center sm:gap-1" aria-label="Bagian proyek">
        @foreach(['overview', 'tasks', 'prd', 'resume'] as $key)
            <a href="{{ $key === 'resume' ? route('projects.resume', $project) : route('projects.show', ['project' => $project, 'section' => $key]) }}"
                class="inline-flex min-h-11 items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-150 motion-reduce:transition-none {{ $section === $key ? 'bg-blue-50 text-blue-700' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}"
                @if($section === $key) aria-current="page" @endif><i class="fa-solid {{ $sections[$key][1] }} w-4 text-center" aria-hidden="true"></i>{{ $sections[$key][0] }}</a>
        @endforeach
        <details data-project-menu class="relative col-span-2 sm:ml-auto" @if(in_array($section, $advanced, true)) data-current="true" @endif>
            <summary aria-label="Menu proyek lainnya" class="flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors hover:bg-zinc-100 [&::-webkit-details-marker]:hidden {{ in_array($section, $advanced, true) ? 'bg-blue-50 text-blue-700' : 'text-zinc-600' }}">
                <i class="fa-solid fa-ellipsis w-4 text-center" aria-hidden="true"></i><span>{{ in_array($section, $advanced, true) ? $sections[$section][0] : 'Lainnya' }}</span><i class="fa-solid fa-chevron-down ml-auto text-[10px] sm:ml-2" aria-hidden="true"></i>
            </summary>
            <div data-project-menu-panel class="absolute right-0 top-full z-50 mt-2 max-h-[40dvh] w-full min-w-56 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-2 shadow-lg sm:w-64 sm:max-h-[60dvh]">
                <p class="px-3 py-2 text-xs font-medium text-zinc-500">Bagian proyek lainnya</p>
                @foreach($advanced as $key)
                    <a href="{{ route('projects.show', ['project' => $project, 'section' => $key]) }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ $section === $key ? 'bg-blue-50 text-blue-700' : 'text-zinc-600 hover:bg-zinc-100' }}" @if($section === $key) aria-current="page" @endif><i class="fa-solid {{ $sections[$key][1] }} w-4 text-center" aria-hidden="true"></i>{{ $sections[$key][0] }}</a>
                @endforeach
                <a href="{{ route('workspaces.members.show', $project->workspace) }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100"><i class="fa-solid fa-users w-4 text-center" aria-hidden="true"></i>Anggota ruang kerja</a>
                <a href="{{ route('projects.insights', $project) }}" class="flex min-h-11 items-center gap-3 rounded-lg border-t border-zinc-100 px-3 py-2.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100"><i class="fa-solid fa-wand-magic-sparkles w-4 text-center" aria-hidden="true"></i>Analisis AI</a>
            </div>
        </details>
    </nav>
