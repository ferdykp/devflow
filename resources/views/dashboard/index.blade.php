@extends('layouts.app')
@section('title', 'Ikhtisar')
@section('page-heading', 'Ikhtisar')
@section('content')
<header class="df-panel mb-5 flex flex-wrap items-end justify-between gap-4 rounded-2xl border p-5 sm:p-6">
    <div class="min-w-0">
        <p class="text-sm font-medium text-[var(--df-muted)]">{{ $today->locale('id')->translatedFormat('l, d F Y') }} <span class="px-1 text-[var(--df-edge)]">/</span> {{ $timezone }}</p>
        <h1 class="df-page-title mt-2">Selamat datang, {{ $user->name }}.</h1>
        <p class="df-page-description mt-2">{{ $nextActivity ? 'Agenda berikutnya '.$nextActivity['event']->title.' pukul '.$nextActivity['starts_at']->format('H:i') : 'Pilih pekerjaan penting dan tentukan langkah berikutnya.' }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('calendar.index', ['date' => $today->toDateString(), 'view' => 'day']) }}" class="df-button-secondary inline-flex min-h-11 items-center gap-2 rounded-xl px-4 text-sm font-semibold"><i class="fa-regular fa-calendar" aria-hidden="true"></i>Kalender</a>
        <a href="{{ route('my-day.index') }}" class="df-button-primary inline-flex min-h-11 items-center gap-2 rounded-xl px-4 text-sm font-semibold"><i class="fa-solid fa-sun" aria-hidden="true"></i>Buka Hari Ini</a>
    </div>
</header>

<div class="df-panel mb-5 flex flex-wrap items-center gap-x-6 gap-y-2 rounded-2xl border p-4 text-sm sm:gap-x-8 sm:p-5">
    <a href="{{ route('tasks.index') }}" class="group inline-flex items-baseline gap-2"><span class="font-display text-2xl font-semibold tracking-tight">{{ $openTasks }}</span><span class="text-[var(--df-muted)] group-hover:text-[var(--df-action)]">tugas terbuka</span></a>
    <span class="hidden h-7 w-px bg-[var(--df-edge)] sm:block" aria-hidden="true"></span>
    <a href="{{ route('focus.index') }}" class="group inline-flex items-baseline gap-2"><span class="font-display text-2xl font-semibold tracking-tight">{{ $priorityTasks->count() }}<span class="text-base text-[var(--df-muted)]">/3</span></span><span class="text-[var(--df-muted)] group-hover:text-[var(--df-action)]">prioritas hari ini</span></a>
    <span class="hidden h-7 w-px bg-[var(--df-edge)] sm:block" aria-hidden="true"></span>
    <a href="{{ route('projects.index') }}" class="group inline-flex items-baseline gap-2"><span class="font-display text-2xl font-semibold tracking-tight">{{ $projectCount }}</span><span class="text-[var(--df-muted)] group-hover:text-[var(--df-action)]">proyek aktif</span></a>
    <span class="hidden h-7 w-px bg-[var(--df-edge)] sm:block" aria-hidden="true"></span>
    <a href="{{ route('learning.index') }}" class="group inline-flex items-baseline gap-2"><span class="font-display text-2xl font-semibold tracking-tight">{{ $learningSessions }}<span class="text-base text-[var(--df-muted)]">m</span></span><span class="text-[var(--df-muted)] group-hover:text-[var(--df-action)]">belajar hari ini</span></a>
</div>

<div class="grid items-start gap-x-10 gap-y-9 xl:grid-cols-[minmax(0,1.55fr)_minmax(280px,.75fr)]">
    <main class="min-w-0 space-y-9">
        <section aria-labelledby="today-priorities" class="df-panel rounded-2xl border p-4 sm:p-5">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-3"><div><p class="mb-1 text-[11px] font-bold uppercase tracking-[.14em] text-[var(--df-secondary)]">Mulai dari sini</p><h2 id="today-priorities" class="font-display text-xl font-semibold tracking-tight">Prioritas hari ini</h2></div><a href="{{ route('focus.index') }}" class="min-h-10 inline-flex items-center gap-2 text-sm font-semibold text-[var(--df-action)]">Atur fokus <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a></div>
            @if($priorityTasks->isEmpty())
                <div class="rounded-2xl border border-dashed border-[var(--df-edge)] bg-[var(--df-paper)] px-5 py-6 sm:px-7"><p class="font-medium">Belum ada prioritas yang dipilih.</p><p class="mt-1 text-sm text-[var(--df-muted)]">Pilih hingga tiga tugas supaya jelas apa yang perlu diselesaikan lebih dulu.</p><a href="{{ route('focus.index') }}" class="mt-4 inline-flex min-h-10 items-center gap-2 font-semibold text-[var(--df-action)]">Pilih prioritas <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a></div>
            @else
                <ol class="divide-y divide-[var(--df-edge)] border-y border-[var(--df-edge)]">
                    @foreach($priorityTasks as $index => $task)
                        <li class="flex items-center gap-3 py-4 sm:gap-4"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-[var(--df-action-soft)] font-mono text-xs font-semibold text-[var(--df-action)]">0{{ $index + 1 }}</span><div class="min-w-0 flex-1"><a href="{{ $task->project ? route('tasks.show', [$task->project, $task]) : route('personal-tasks.show', $task) }}" class="block truncate text-sm font-semibold hover:text-[var(--df-action)]">{{ $task->title }}</a><p class="mt-1 truncate text-xs text-[var(--df-muted)]">{{ $task->project?->name ?? 'Pribadi' }} <span class="px-1">·</span> {{ $task->due_date?->format('d M') ?? 'Tanpa tenggat' }}</p></div><form method="POST" action="{{ route('my-day.tasks.complete', $task) }}" data-async-form data-async-action="simple-task-complete">@csrf<button class="min-h-10 rounded-lg border border-[var(--df-edge)] px-3 text-xs font-semibold hover:border-emerald-500 hover:text-emerald-700">Selesai</button></form></li>
                    @endforeach
                </ol>
            @endif
        </section>

        <section aria-labelledby="today-agenda" class="df-panel rounded-2xl border p-4 sm:p-5">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-3"><div><p class="mb-1 text-[11px] font-bold uppercase tracking-[.14em] text-[var(--df-secondary)]">Rencana waktu</p><h2 id="today-agenda" class="font-display text-xl font-semibold tracking-tight">Agenda hari ini</h2></div><a href="{{ route('calendar.index', ['date' => $today->toDateString(), 'view' => 'day']) }}" class="min-h-10 inline-flex items-center gap-2 text-sm font-semibold text-[var(--df-action)]">Buka kalender <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a></div>
            <p class="mb-3 text-xs text-[var(--df-muted)]">{{ count($todayAgenda) }} blok waktu <span class="px-1">·</span> {{ $conflicts }} benturan</p>
            @if($todayAgenda)
                <ol class="relative ml-2 border-l border-[var(--df-edge)]">
                    @foreach($todayAgenda as $item)
                        <li class="relative flex gap-4 pb-4 pl-5 last:pb-0"><span class="absolute -left-[5px] top-1.5 size-2.5 rounded-full border-2 border-[var(--df-action)] bg-[var(--df-paper)]"></span><time class="w-14 shrink-0 pt-0.5 font-mono text-xs font-semibold text-[var(--df-action)]">{{ $item['event']->all_day ? 'Sepanjang hari' : $item['starts_at']->format('H:i') }}</time><div class="min-w-0"><p class="text-sm font-semibold">{{ $item['event']->title }}</p><p class="mt-1 text-xs text-[var(--df-muted)]">{{ $item['event']->task?->title ?? $item['event']->lesson?->title ?? $item['event']->routine?->title ?? ($item['event']->is_flexible ? 'Waktu fleksibel' : 'Agenda') }}</p></div></li>
                    @endforeach
                </ol>
            @else
                <div class="rounded-xl bg-[var(--df-paper)] px-4 py-4 text-sm text-[var(--df-muted)]">Kalender masih kosong hari ini. Tenggat tugas ada di daftar pekerjaan mendatang.</div>
            @endif
        </section>

        <section aria-labelledby="upcoming-work" class="df-panel rounded-2xl border p-4 sm:p-5">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-3"><div><p class="mb-1 text-[11px] font-bold uppercase tracking-[.14em] text-[var(--df-secondary)]">Yang perlu diperhatikan</p><h2 id="upcoming-work" class="font-display text-xl font-semibold tracking-tight">Tugas mendatang</h2></div><a href="{{ route('tasks.index', ['status' => 'open']) }}" class="min-h-10 inline-flex items-center gap-2 text-sm font-semibold text-[var(--df-action)]">Lihat tugas terbuka <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a></div>
            <div class="divide-y divide-[var(--df-edge)] border-y border-[var(--df-edge)]">
                @forelse($upcomingTasks as $task)
                    <a href="{{ $task->project ? route('tasks.show', [$task->project, $task]) : route('personal-tasks.show', $task) }}" class="flex min-h-14 items-center justify-between gap-4 py-3"><span class="min-w-0"><span class="block truncate text-sm font-medium">{{ $task->title }}</span><span class="mt-1 block truncate text-xs text-[var(--df-muted)]">{{ $task->project?->name ?? 'Pribadi' }}</span></span><span class="shrink-0 text-xs font-medium text-[var(--df-muted)]">{{ $task->due_date?->format('D, d M') }}</span></a>
                @empty
                    <p class="py-4 text-sm text-[var(--df-muted)]">Tidak ada tenggat dalam tujuh hari ke depan.</p>
                @endforelse
            </div>
        </section>
    </main>

    <aside class="min-w-0 space-y-8 xl:border-l xl:border-[var(--df-edge)] xl:pl-8">
        <section aria-labelledby="project-activity" class="df-panel rounded-2xl border p-4"><div class="mb-3 flex items-center justify-between gap-2"><h2 id="project-activity" class="font-display text-lg font-semibold">Aktivitas proyek</h2><a href="{{ route('projects.index') }}" class="text-xs font-semibold text-[var(--df-action)]">Semua</a></div>@forelse($activities as $activity)<div class="border-t border-[var(--df-edge)] py-3 first:border-0"><p class="text-sm font-medium">{{ $activity->title }}</p><p class="mt-1 text-xs text-[var(--df-muted)]">{{ $activity->project->name }} · {{ $activity->occurred_at->diffForHumans() }}</p></div>@empty<p class="border-t border-[var(--df-edge)] py-3 text-sm text-[var(--df-muted)]">Belum ada aktivitas terbaru.</p>@endforelse</section>

        <section aria-labelledby="projects-to-resume" class="df-panel rounded-2xl border p-4"><div class="mb-2 flex items-center justify-between gap-2"><h2 id="projects-to-resume" class="font-display text-lg font-semibold">Lanjutkan proyek</h2><a href="{{ route('projects.index') }}" class="text-xs font-semibold text-[var(--df-action)]">Semua</a></div>@forelse($projects as $project)<a href="{{ route('projects.show', $project) }}" class="flex min-h-12 items-center justify-between gap-3 border-t border-[var(--df-edge)] py-3 first:border-0"><span class="min-w-0 truncate text-sm font-medium">{{ $project->name }}</span><span class="shrink-0 text-xs text-[var(--df-muted)]">{{ $project->workspace->name }}</span></a>@empty<p class="border-t border-[var(--df-edge)] py-3 text-sm text-[var(--df-muted)]">Buat proyek untuk mulai bekerja bersama tim.</p>@endforelse</section>

        <section aria-labelledby="routine-checkin" class="df-panel rounded-2xl border p-4"><div class="mb-1 flex items-center justify-between gap-2"><h2 id="routine-checkin" class="font-display text-lg font-semibold">Rutinitas</h2><a href="{{ route('routines.index') }}" class="text-xs font-semibold text-[var(--df-action)]">Kelola</a></div><p class="mb-2 text-xs text-[var(--df-muted)]">{{ $routineCompleted }} dari {{ $routines->count() }} selesai hari ini</p>@forelse($routines as $routine)<div class="flex items-center justify-between gap-3 border-t border-[var(--df-edge)] py-3"><span class="truncate text-sm">{{ $routine->title }}</span><span class="shrink-0 text-xs {{ $routine->checkIns->contains('status', 'completed') ? 'text-emerald-700' : 'text-[var(--df-muted)]' }}">{{ $routine->checkIns->contains('status', 'completed') ? 'Selesai' : 'Belum dicatat' }}</span></div>@empty<p class="border-t border-[var(--df-edge)] py-3 text-sm text-[var(--df-muted)]">Tambahkan rutinitas untuk membangun kebiasaan.</p>@endforelse</section>

        <section aria-labelledby="growth-progress" class="df-panel rounded-2xl border p-4"><div class="mb-1 flex items-center justify-between gap-2"><h2 id="growth-progress" class="font-display text-lg font-semibold">Belajar &amp; berkembang</h2><a href="{{ route('learning.index') }}" class="text-xs font-semibold text-[var(--df-action)]">Learning Hub</a></div><p class="text-sm leading-6 text-[var(--df-muted)]">{{ $learningCommitments }} sesi terjadwal · {{ $learningSessions }} menit tercatat hari ini</p>@if($goals->isNotEmpty())<ul class="mt-3 divide-y divide-[var(--df-edge)] border-t border-[var(--df-edge)]">@foreach($goals as $goal)<li class="py-3"><p class="text-sm font-medium">{{ $goal->title }}</p><p class="mt-1 text-xs text-[var(--df-muted)]">@if($goal->progress_mode === 'numeric'){{ $goal->current_value }} / {{ $goal->target_value }} {{ $goal->unit }}@elseif($goal->milestones_total > 0){{ $goal->milestones_completed }} / {{ $goal->milestones_total }} milestone @else Progres melalui bukti dan milestone @endif</p></li>@endforeach</ul>@else<p class="mt-3 text-sm text-[var(--df-muted)]">Tambahkan goal untuk menautkan aktivitas belajar dan tugas.</p>@endif</section>

        <section class="rounded-2xl bg-[var(--df-action-soft)] p-4"><div class="flex items-start justify-between gap-3"><div><h2 class="font-semibold text-[var(--df-action)]">Smart Coach</h2><p class="mt-1 text-sm leading-5 text-[var(--df-muted)]">{{ $recommendationCount ? $recommendationCount.' saran menunggu konfirmasi' : 'Tinjau saran dari data aktivitasmu.' }}</p></div><i class="fa-solid fa-wand-magic-sparkles mt-1 text-[var(--df-action)]" aria-hidden="true"></i></div><a href="{{ route('smart-coach.index') }}" class="mt-3 inline-flex min-h-10 items-center gap-2 text-sm font-semibold text-[var(--df-action)]">Tinjau saran <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a></section>
        <p class="df-panel rounded-xl border px-3 py-3 text-xs leading-5 text-[var(--df-muted)]">{{ $conflicts ? $conflicts.' benturan agenda perlu ditinjau.' : 'Tidak ada blok waktu yang bertumpuk.' }} Hari tanpa agenda tercatat belum tentu berarti kamu tersedia.</p>
    </aside>
</div>
@endsection
