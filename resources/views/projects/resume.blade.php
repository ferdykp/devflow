@extends('layouts.app')
@section('title', 'Lanjutkan Proyek — '.$project->name)
@section('page-heading', $project->name)
@section('content')
@include('projects._workspace_navigation', ['section' => 'resume'])
<section class="relative min-w-0 w-full min-h-[400px]" aria-label="Lanjutkan Proyek">
<div class="space-y-6">
    <header class="df-section-header [display:flex] [align-items:end] [justify-content:space-between] [gap:20px] [margin-bottom:22px] max-[650px]:[align-items:start] max-[650px]:[flex-direction:column]">
        <div>
            <p class="df-kicker [text-transform:uppercase] [color:#757983] [font-size:10px] [letter-spacing:.15em] [font-weight:750] [margin-bottom:12px]">LANJUTKAN PROYEK</p>
            <h2 class="df-section-title [font-size:24px] [letter-spacing:-.045em] [line-height:1.3] [font-weight:650]">Terakhir sampai mana?</h2>
            <p class="mt-2 text-sm text-[#747571]">Berdasarkan aktivitas, tugas, dan catatan yang tersedia di DevFlow.</p>
        </div>
    </header>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.panel><p class="text-xs text-[#747571]">Tugas belum selesai</p><p class="mt-2 df-heading [font-size:clamp(1.65rem,2.8vw,2.15rem)] [font-weight:650] [letter-spacing:-.045em] [line-height:1.18]">{{ $resume['task_count'] - $resume['done_count'] }}</p></x-ui.panel>
        <x-ui.panel><p class="text-xs text-[#747571]">Penyelesaian tugas</p><p class="mt-2 df-heading [font-size:clamp(1.65rem,2.8vw,2.15rem)] [font-weight:650] [letter-spacing:-.045em] [line-height:1.18]">{{ $resume['progress'] === null ? 'Belum bisa dihitung' : $resume['progress'].'%' }}</p></x-ui.panel>
        <x-ui.panel><p class="text-xs text-[#747571]">Aktivitas terakhir yang tercatat</p><p class="mt-2 text-sm font-semibold">{{ $resume['last_activity']?->occurred_at?->diffForHumans() ?? 'Belum ada aktivitas' }}</p></x-ui.panel>
    </div>
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.4fr)_minmax(280px,1fr)]">
        <div class="space-y-5">
            <x-ui.panel>
                <h2 class="text-base font-semibold">Lanjutkan pekerjaan</h2>
                @if($resume['next_task'])
                    <p class="mt-3 text-sm font-medium">{{ $resume['next_task']->title }}</p>
                    <p class="mt-1 text-xs text-[#747571]">{{ \App\Support\UiLabel::status($resume['next_task']->status) }} · {{ \App\Support\UiLabel::priority($resume['next_task']->priority) }}</p>
                    <p class="mt-3 text-xs text-[#747571]">Dipilih dari tugas yang belum selesai; bukan laporan kemajuan otomatis.</p>
                @else
                    <p class="mt-3 text-sm text-[#747571]">Belum ada tugas yang bisa dikerjakan. Tambahkan tugas atau lihat catatan proyek.</p>
                @endif
            </x-ui.panel>
            <x-ui.panel>
                <h2 class="text-base font-semibold">Aktivitas terbaru</h2>
                <div class="mt-3 divide-y divide-zinc-100">
                    @forelse($resume['activities'] as $activity)
                        <div class="py-3"><p class="text-sm font-medium">{{ $activity->title }}</p><p class="mt-1 text-xs text-[#747571]">{{ ucfirst($activity->source) }} · {{ $activity->occurred_at->diffForHumans() }}</p></div>
                    @empty
                        <p class="py-3 text-sm text-[#747571]">Belum ada aktivitas tersinkron. Perubahan lokal yang belum dikirim ke GitHub tidak akan muncul di sini.</p>
                    @endforelse
                </div>
            </x-ui.panel>
            <x-ui.panel>
                <h2 class="text-base font-semibold">Catatan terbaru</h2>
                <div class="mt-3 divide-y divide-zinc-100">
                    @forelse($resume['notes'] as $note)
                        <div class="py-3"><p class="text-sm font-medium">{{ $note->title }}</p><p class="mt-1 whitespace-pre-wrap text-sm text-[#60615e]">{{ $note->content }}</p></div>
                    @empty<p class="py-3 text-sm text-[#747571]">Belum ada catatan.</p>@endforelse
                </div>
            </x-ui.panel>
        </div>
        <div class="space-y-5">
            <x-ui.panel>
                <h2 class="text-base font-semibold">Pekerjaan berjalan</h2>
                <div class="mt-3 divide-y divide-zinc-100">
                    @forelse($resume['open_tasks'] as $task)
                        <div class="py-3"><p class="text-sm font-medium">{{ $task->title }}</p><p class="mt-1 text-xs text-[#747571]">{{ \App\Support\UiLabel::status($task->status) }} · {{ \App\Support\UiLabel::priority($task->priority) }}</p></div>
                    @empty<p class="py-3 text-sm text-[#747571]">Tidak ada tugas aktif.</p>@endforelse
                </div>
            </x-ui.panel>
            <x-ui.panel>
                <h2 class="text-base font-semibold">Tahapan</h2>
                <div class="mt-3 divide-y divide-zinc-100">
                    @forelse($resume['milestones'] as $milestone)
                        <div class="py-3"><p class="text-sm font-medium">{{ $milestone->title }}</p><p class="mt-1 text-xs text-[#747571]">{{ $milestone->completed_tasks_count }} dari {{ $milestone->tasks_count }} tugas selesai</p></div>
                    @empty<p class="py-3 text-sm text-[#747571]">Belum ada tahapan. Bagian ini tidak wajib diisi.</p>@endforelse
                </div>
            </x-ui.panel>
            <x-ui.panel>
                <h2 class="text-base font-semibold">Sumber data</h2>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-[#747571]">GitHub</dt><dd>{{ $resume['github_connected'] ? 'Terhubung' : 'Belum terhubung' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-[#747571]">Sinkronisasi terakhir</dt><dd>{{ $resume['last_sync'] ? \Illuminate\Support\Carbon::parse($resume['last_sync'])->diffForHumans() : 'Belum tersedia' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-[#747571]">PRD</dt><dd>{{ $resume['has_prd'] ? 'Tersedia' : 'Belum ditambahkan' }}</dd></div>
                </dl>
                <p class="mt-3 text-xs leading-5 text-[#747571]">Ringkasan ini dibuat dari data yang tersimpan, tanpa analisis AI atau pemindaian Git lokal.</p>
            </x-ui.panel>
        </div>
    </div>
</div>
</section>
</div>
@endsection
