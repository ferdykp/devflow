@extends('layouts.app')
@section('title', 'Hari Ini')
@section('page-heading', 'Hari Ini')
@section('content')
<x-ui.page-heading title="Hari ini, satu langkah demi satu langkah." description="Prioritas, agenda, dan tugas yang dijadwalkan untuk {{ $date->locale('id')->translatedFormat('l, d F Y') }} · {{ $timezone }}." />

<div class="mb-7 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap items-center gap-2">
        <div class="flex items-center gap-1 rounded-xl border border-[#e2e7ee] bg-white p-1 shadow-sm">
            <a aria-label="Hari sebelumnya" href="{{ route('my-day.index', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="grid size-10 place-items-center rounded-lg text-zinc-600 hover:bg-[#eff4ff]"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a>
            <span class="px-2 text-sm font-semibold text-zinc-800">{{ $date->locale('id')->translatedFormat('D, d F Y') }}</span>
            <a aria-label="Hari selanjutnya" href="{{ route('my-day.index', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="grid size-10 place-items-center rounded-lg text-zinc-600 hover:bg-[#eff4ff]"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
        </div>
        <a href="{{ route('my-day.index') }}" class="inline-flex min-h-11 items-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-medium text-zinc-700 hover:bg-[#f8f9ff]">Hari ini</a>
    </div>
    <div class="flex flex-wrap items-center gap-2 rounded-xl border border-[#e2e7ee] bg-white px-3 py-2 text-xs shadow-sm">
        <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-700"><span class="size-2 rounded-full bg-emerald-600"></span>{{ $completedToday }} selesai</span>
        <span class="h-4 w-px bg-zinc-200" aria-hidden="true"></span>
        <span class="inline-flex items-center gap-1.5 text-zinc-600"><span class="size-2 rounded-full bg-[#2463eb]"></span>{{ $tasks->count() }} tersisa</span>
        <span class="h-4 w-px bg-zinc-200" aria-hidden="true"></span>
        <a href="{{ route('calendar.index', ['date' => $localDate, 'view' => 'day']) }}" class="font-medium text-[#004bc6] hover:underline">{{ $events->count() }} agenda</a>
    </div>
</div>

<x-ui.panel class="mb-7 overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3"><span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-700"><i class="fa-solid fa-chart-simple" aria-hidden="true"></i></span><div><h2 class="text-sm font-semibold">Kapasitas hari ini</h2><p class="mt-1 text-xs text-zinc-500">{{ intdiv($plannedMinutes, 60) }}j {{ $plannedMinutes % 60 }}m terencana dari {{ intdiv($availableMinutes, 60) }}j tersedia</p></div></div>
        <span class="rounded-full bg-zinc-100 px-3 py-1.5 text-xs font-semibold text-zinc-700">{{ $plannedLoadPercent }}% terisi</span>
    </div>
    <div class="mt-4 h-2 overflow-hidden rounded-full bg-zinc-100"><div class="h-full rounded-full {{ $plannedLoadPercent > 90 ? 'bg-amber-500' : 'bg-blue-600' }} transition-all" style="width: {{ $plannedLoadPercent }}%"></div></div>
</x-ui.panel>

<section aria-labelledby="today-priorities" class="mb-8">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2.5"><span class="grid size-8 place-items-center rounded-lg bg-[#e9efff] text-[#004bc6]"><i class="fa-solid fa-star" aria-hidden="true"></i></span><div><h2 id="today-priorities" class="text-lg font-semibold tracking-tight">{{ $priorityTasks->count() }} prioritas utama</h2><p class="text-xs text-zinc-500">Pilihan fokusmu hari ini, maksimal tiga tugas.</p></div></div>
        <a href="{{ route('focus.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg px-3 text-sm font-medium text-zinc-600 hover:bg-white hover:text-[#004bc6]"><i class="fa-solid fa-arrow-up-wide-short" aria-hidden="true"></i>Atur prioritas</a>
    </div>
    @if ($priorityTasks->isNotEmpty())
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($priorityTasks as $index => $task)
                <article class="df-panel relative overflow-hidden p-4 pl-5">
                    <span class="absolute inset-y-0 left-0 w-1 {{ $task->priority === 'high' ? 'bg-red-500' : ($task->priority === 'low' ? 'bg-[#2463eb]' : 'bg-amber-500') }}" aria-hidden="true"></span>
                    <div class="mb-3 flex items-center justify-between gap-2"><span class="rounded-md px-2 py-1 text-[11px] font-semibold {{ $task->priority === 'high' ? 'bg-red-50 text-red-700' : ($task->priority === 'low' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-800') }}">P{{ $index + 1 }} · {{ $task->priority === 'high' ? 'Tinggi' : ($task->priority === 'low' ? 'Rendah' : 'Normal') }}</span>@if ($task->estimated_minutes)<span class="inline-flex items-center gap-1 text-xs text-zinc-500"><i class="fa-regular fa-clock" aria-hidden="true"></i>{{ $task->estimated_minutes }} mnt</span>@endif</div>
                    <div class="flex items-start gap-3"><form method="POST" action="{{ route('my-day.tasks.complete', $task) }}">@csrf<button class="mt-0.5 grid size-5 place-items-center rounded border border-zinc-300 text-transparent hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700" aria-label="Tandai selesai: {{ $task->title }}"><i class="fa-solid fa-check text-xs" aria-hidden="true"></i></button></form><div class="min-w-0 flex-1"><h3 class="font-semibold leading-5 text-zinc-900">{{ $task->title }}</h3><p class="mt-1 line-clamp-2 text-xs leading-5 text-zinc-500">{{ $task->project?->name ?? $task->personalGoal?->title ?? 'Tugas pribadi' }}</p></div></div>
                </article>
            @endforeach
        </div>
    @else
        <div class="df-panel flex flex-wrap items-center justify-between gap-3 p-4"><p class="text-sm text-zinc-600">Belum ada prioritas yang dipilih.</p><a href="{{ route('focus.index') }}" class="text-sm font-semibold text-[#004bc6]">Pilih fokus hari ini <span aria-hidden="true">→</span></a></div>
    @endif
</section>

<div class="space-y-5">
    <section class="min-w-0 space-y-4" aria-labelledby="today-agenda">
        <div class="flex flex-wrap items-end justify-between gap-3"><div><h2 id="today-agenda" class="text-xl font-semibold tracking-tight">Agenda dan tugas terjadwal</h2><p class="mt-1 text-sm text-zinc-500">Urutan waktu berdasarkan zona {{ $timezone }}.</p></div><div class="flex gap-2"><a href="{{ route('calendar.index', ['date' => $localDate, 'view' => 'day']) }}" class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-medium text-[#004bc6] hover:bg-white">Buka kalender</a></div></div>
        <div class="space-y-2">
            @forelse ($agendaItems as $agendaItem)
                @php($item = $agendaItem['model'])
                <article class="df-panel flex min-w-0 gap-3 p-4 sm:gap-4">
                    <div class="w-[76px] shrink-0 pt-0.5"><time class="block text-xs font-semibold tabular-nums text-[#004bc6]">{{ $agendaItem['starts_at']->format('H:i') }}</time><span class="mt-1 block text-[10px] text-zinc-500">{{ $agendaItem['ends_at']->format('H:i') }}</span></div>
                    <span class="mt-1 h-10 w-1 shrink-0 rounded-full {{ $agendaItem['kind'] === 'event' ? ($item->is_flexible ? 'bg-amber-500' : 'bg-[#2463eb]') : 'bg-emerald-600' }}" aria-hidden="true"></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-start justify-between gap-2"><div class="min-w-0"><div class="mb-1 flex flex-wrap items-center gap-2"><span class="rounded px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $agendaItem['kind'] === 'event' ? ($item->is_flexible ? 'bg-amber-50 text-amber-800' : 'bg-blue-50 text-blue-700') : 'bg-emerald-50 text-emerald-700' }}">{{ $agendaItem['kind'] === 'event' ? ($item->is_flexible ? 'Fleksibel' : 'Agenda') : 'Tugas' }}</span>@if ($agendaItem['kind'] === 'task' && $item->priority === 'high')<span class="text-[10px] font-semibold text-red-700">Prioritas tinggi</span>@endif</div><h3 class="break-words text-sm font-semibold text-zinc-900">{{ $item->title }}</h3><p class="mt-1 text-xs leading-5 text-zinc-500">{{ $agendaItem['kind'] === 'task' ? ($item->project?->name ?? 'Tugas pribadi') : ($item->description ?: 'Agenda personal') }}</p></div>
                            @if ($agendaItem['kind'] === 'task')<form method="POST" action="{{ route('my-day.tasks.complete', $item) }}">@csrf<button class="min-h-10 rounded-lg px-3 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Tandai selesai</button></form>@else<form method="POST" action="{{ route('calendar.events.complete', $item) }}">@csrf<input type="hidden" name="original_start" value="{{ $item->starts_at->utc()->format('Y-m-d H:i:s') }}"><button class="min-h-10 rounded-lg px-3 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Tandai selesai</button></form>@endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="df-panel px-5 py-10 text-center"><span class="mx-auto grid size-11 place-items-center rounded-full bg-[#eff4ff] text-[#2463eb]"><i class="fa-regular fa-calendar" aria-hidden="true"></i></span><h3 class="mt-3 font-semibold">Belum ada yang dijadwalkan</h3><p class="mt-1 text-sm text-zinc-500">Tambahkan agenda atau jadwalkan tugas dari Inbox.</p><a href="{{ route('calendar.index', ['date' => $localDate, 'view' => 'day']) }}" class="mt-3 inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-[#004bc6] hover:bg-[#eff4ff]">Buka kalender</a></div>
            @endforelse
        </div>
    </section>

    <aside class="space-y-4">
        <x-ui.panel>
            <div class="flex items-center justify-between gap-3"><div><h2 class="font-semibold">Belum dijadwalkan</h2><p class="mt-1 text-xs text-zinc-500">Tugas hari ini yang belum punya waktu.</p></div><span class="rounded-full bg-[#eff4ff] px-2.5 py-1 text-xs font-semibold text-[#004bc6]">{{ $unscheduledTasks->count() }}</span></div>
            <ul class="mt-3 divide-y divide-zinc-100">
                @forelse ($unscheduledTasks as $task)
                    <li class="py-3"><div class="flex items-start justify-between gap-2"><div class="min-w-0"><p class="break-words text-sm font-medium">{{ $task->title }}</p><p class="mt-1 text-xs text-zinc-500">{{ $task->project?->name ?? 'Pribadi' }} · {{ $task->due_date?->format('d/m') ?? 'Tanpa tenggat' }}</p></div><details class="shrink-0"><summary class="grid size-9 cursor-pointer list-none place-items-center rounded-lg text-zinc-500 hover:bg-zinc-100 [&::-webkit-details-marker]:hidden" aria-label="Jadwalkan ulang {{ $task->title }}"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></summary><form method="POST" action="{{ route('my-day.tasks.defer', $task) }}" class="absolute right-4 z-10 mt-1 grid w-64 gap-2 rounded-xl border border-zinc-200 bg-white p-3 shadow-lg">@csrf<x-ui.field label="Jadwalkan ulang ke" name="due_date" type="date" :value="$date->copy()->addDay()->format('Y-m-d')" required /><x-ui.button variant="secondary" class="w-full">Simpan tanggal</x-ui.button></form></details></div></li>
                @empty
                    <li class="py-4 text-sm text-zinc-500">Semua tugas hari ini sudah memiliki waktu atau sudah masuk agenda.</li>
                @endforelse
            </ul>
            <a href="{{ route('tasks.index') }}" class="mt-2 inline-flex min-h-10 items-center text-sm font-semibold text-[#004bc6]">Lihat semua tugas <span class="ml-1" aria-hidden="true">→</span></a>
        </x-ui.panel>

        <x-ui.panel>
            <div class="flex items-center justify-between gap-3"><div><h2 class="font-semibold">Rutinitas</h2><p class="mt-1 text-xs text-zinc-500">{{ $routines->where('checkIns.0.status', 'completed')->count() }} dari {{ $routines->count() }} selesai hari ini</p></div><a href="{{ route('routines.index') }}" class="text-xs font-semibold text-[#004bc6]">Kelola</a></div>
            <ul class="mt-3 space-y-2">
                @forelse ($routines as $routine)
                    @php($checkIn = $routine->checkIns->first())
                    <li class="flex items-center justify-between gap-2 rounded-xl bg-[#f8f9ff] p-3"><div class="min-w-0"><p class="truncate text-sm font-medium">{{ $routine->title }}</p><p class="mt-1 text-xs text-zinc-500">{{ $routine->preferred_time ? substr($routine->preferred_time, 0, 5).' · ' : '' }}{{ $routine->estimated_minutes }} menit</p></div>
                        @if ($checkIn)<span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $checkIn->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-600' }}">{{ $checkIn->status === 'completed' ? 'Selesai' : 'Dilewati' }}</span>@else<form method="POST" action="{{ route('my-day.routines.check-in', $routine) }}">@csrf<input type="hidden" name="status" value="completed"><input type="hidden" name="activity_level" value="normal"><button class="min-h-9 rounded-lg bg-white px-3 text-xs font-semibold text-[#004bc6] shadow-sm">Catat</button></form>@endif
                    </li>
                @empty<li class="py-3 text-sm text-zinc-500">Tidak ada rutinitas terjadwal hari ini.</li>@endforelse
            </ul>
        </x-ui.panel>

        @if ($sessions->isNotEmpty())
            <x-ui.panel><div class="flex items-center justify-between"><h2 class="font-semibold">Belajar hari ini</h2><a href="{{ route('learning.index') }}" class="text-xs font-semibold text-[#004bc6]">Learning Hub</a></div><ul class="mt-2 divide-y divide-zinc-100">@foreach ($sessions as $session)<li class="flex justify-between gap-3 py-2 text-xs"><span class="min-w-0 truncate">{{ $session->lesson?->title ?? $session->path->title }}</span><strong class="shrink-0">{{ $session->duration_minutes }} mnt</strong></li>@endforeach</ul></x-ui.panel>
        @endif

        <x-ui.panel><h2 class="font-semibold">Catatan hari ini</h2><p class="mt-1 text-xs text-zinc-500">Simpan refleksi singkat untuk review mingguan.</p><details class="mt-3"><summary class="flex min-h-10 cursor-pointer items-center rounded-lg px-2 text-sm font-semibold text-[#004bc6] hover:bg-[#eff4ff]">{{ $reflection?->reflection ? 'Edit refleksi' : 'Tulis refleksi' }}</summary><form method="POST" action="{{ route('my-day.reflection') }}" class="mt-3 space-y-3">@csrf<input type="hidden" name="reflection_date" value="{{ $localDate }}"><x-ui.textarea label="Apa yang berjalan baik? Apa yang dibawa ke besok?" name="reflection" rows="4" :value="$reflection?->reflection" /><x-ui.button variant="secondary" class="w-full">Simpan refleksi</x-ui.button></form></details></x-ui.panel>
    </aside>
</div>
@endsection
