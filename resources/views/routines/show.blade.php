@extends('layouts.app')
@section('title', $routine->title)
@section('page-heading', 'Detail rutinitas')
@section('content')
<x-ui.page-heading eyebrow="Rutinitas" :title="$routine->title" description="Jadwal, versi minimum, dan riwayat check-in.">
    <a href="{{ route('routines.index') }}" class="text-sm font-medium text-blue-700">Kembali ke rutinitas</a>
    <button type="button" data-create-open="routine-edit" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>Edit rutinitas
    </button>
</x-ui.page-heading>

<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(280px,.75fr)]">
    <div class="space-y-5">
        <x-ui.panel>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs text-emerald-700">{{ $routine->status === 'active' ? 'Aktif' : ucfirst($routine->status) }}</span>
                    <p class="mt-4 whitespace-pre-wrap text-sm leading-6 text-zinc-600">{{ $routine->description ?: 'Bangun konsistensi dengan tindakan kecil yang bisa diulang.' }}</p>
                </div>
                <div class="rounded-xl bg-blue-50 px-4 py-3 text-center"><strong class="block text-xl text-blue-700">{{ $routine->target_per_week }}×</strong><span class="text-xs text-blue-700">target per minggu</span></div>
            </div>
            <dl class="mt-5 grid gap-3 border-t border-zinc-100 pt-4 sm:grid-cols-3">
                <div><dt class="text-xs text-zinc-500">Frekuensi</dt><dd class="mt-1 text-sm">{{ $routine->frequency === 'daily' ? 'Harian' : implode(', ', array_map(fn ($day) => [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'][$day], $routine->days_of_week ?? [])) }}</dd></div>
                <div><dt class="text-xs text-zinc-500">Durasi</dt><dd class="mt-1 text-sm">{{ $routine->estimated_minutes ?: '—' }} menit</dd></div>
                <div><dt class="text-xs text-zinc-500">Jam pilihan</dt><dd class="mt-1 text-sm">{{ $routine->preferred_time ? substr($routine->preferred_time, 0, 5) : 'Fleksibel' }}</dd></div>
            </dl>
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                @foreach(['minimum' => 'Versi minimum', 'normal' => 'Versi normal', 'extended' => 'Versi ekstra'] as $field => $label)
                    <div class="rounded-xl bg-zinc-50 p-3"><p class="text-xs font-medium text-zinc-500">{{ $label }}</p><p class="mt-1 text-sm">{{ $routine->{$field.'_action'} ?: 'Belum ditentukan' }}</p></div>
                @endforeach
            </div>
        </x-ui.panel>

        <x-ui.panel>
            <h2 class="font-semibold">Riwayat check-in</h2>
            <ul class="mt-3 divide-y divide-zinc-100">
                @forelse($routine->checkIns as $checkIn)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3"><span class="text-sm">{{ $checkIn->occurred_on->locale('id')->translatedFormat('l, d F Y') }}</span><span class="text-xs {{ $checkIn->status === 'completed' ? 'text-emerald-700' : 'text-zinc-500' }}">{{ $checkIn->status === 'completed' ? 'Selesai' : 'Dilewati' }} @if($checkIn->activity_level) · {{ ucfirst($checkIn->activity_level) }} @endif @if($checkIn->duration_minutes) · {{ $checkIn->duration_minutes }} menit @endif</span></li>
                @empty
                    <li class="py-4 text-sm text-zinc-500">Belum ada check-in. Catatan aktivitas akan muncul di sini.</li>
                @endforelse
            </ul>
        </x-ui.panel>
    </div>

    <aside class="space-y-4">
        <x-ui.panel>
            <h2 class="font-semibold">Tindakan hari ini</h2>
            <div class="mt-3 grid grid-cols-2 gap-2">
                @foreach(['completed' => 'Selesai', 'skipped' => 'Lewati'] as $status => $label)
                    <form method="POST" action="{{ route('routines.check-in', $routine) }}">
                        @csrf
                        <input type="hidden" name="status" value="{{ $status }}">
                        <button class="min-h-11 flex-1 rounded-lg border px-3 text-sm font-semibold transition focus-visible:outline-2 {{ $status === 'completed' ? 'border-blue-600 bg-blue-600 text-white hover:bg-blue-700 focus-visible:outline-blue-700' : 'border-zinc-200 bg-zinc-100 text-zinc-700 hover:border-zinc-300 hover:bg-zinc-200 focus-visible:outline-zinc-700' }}">{{ $label }}</button>
                    </form>
                @endforeach
            </div>
        </x-ui.panel>
        <x-ui.panel>
            <h2 class="font-semibold">Status rutinitas</h2>
            <p class="mt-2 text-sm text-zinc-600">{{ $routine->status === 'active' ? 'Rutinitas aktif dan siap diikuti.' : ($routine->status === 'paused' ? 'Rutinitas sedang dijeda.' : 'Rutinitas diarsipkan.') }}</p>
            <button type="button" data-create-open="routine-edit" class="mt-3 inline-flex min-h-10 items-center rounded-lg border border-zinc-200 bg-white px-3 text-sm font-semibold text-zinc-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-blue-700">Ubah status</button>
        </x-ui.panel>
    </aside>
</div>

<x-ui.create-dialog type="routine-edit" title="Edit rutinitas" description="Perbarui jadwal, tindakan, dan status rutinitas." :errors="$errors->any() && old('_form') === 'edit-routine'">
    <form method="POST" action="{{ route('routines.update', $routine) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @csrf
        @method('PATCH')
        <input type="hidden" name="_form" value="edit-routine">
        <x-ui.field label="Nama" name="title" :value="$routine->title" required />
        <x-ui.select label="Status" name="status" :options="['active' => 'Aktif', 'paused' => 'Dijeda', 'archived' => 'Diarsipkan']" :value="$routine->status" />
        <x-ui.textarea wrapper-class="sm:col-span-2" label="Deskripsi" name="description" rows="2" :value="$routine->description" />
        <x-ui.select label="Frekuensi" name="frequency" :options="['daily' => 'Harian', 'weekly' => 'Mingguan']" :value="$routine->frequency" />
        <x-ui.field label="Target per minggu" name="target_per_week" type="number" min="1" max="7" :value="$routine->target_per_week" required />
        <fieldset class="space-y-2 sm:col-span-2">
            <legend class="text-sm font-medium">Hari dalam jadwal mingguan</legend>
            <div class="grid grid-cols-4 gap-2">
                @foreach([1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'] as $day => $label)
                    <label class="flex min-h-10 items-center gap-2 rounded-lg border border-zinc-200 px-2 text-xs"><input type="checkbox" name="days_of_week[]" value="{{ $day }}" @checked(in_array($day, old('days_of_week', $routine->days_of_week ?? [])))>{{ $label }}</label>
                @endforeach
            </div>
            @error('days_of_week')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
        </fieldset>
        <x-ui.field label="Jam" name="preferred_time" type="time" :value="$routine->preferred_time" />
        <x-ui.field label="Durasi menit" name="estimated_minutes" type="number" min="1" max="1440" :value="$routine->estimated_minutes" />
        <x-ui.field label="Tindakan minimum" name="minimum_action" :value="$routine->minimum_action" />
        <x-ui.field label="Tindakan normal" name="normal_action" :value="$routine->normal_action" />
        <x-ui.field wrapper-class="sm:col-span-2" label="Tindakan ekstra" name="extended_action" :value="$routine->extended_action" />
        <div class="flex justify-end sm:col-span-2"><x-ui.button>Simpan perubahan</x-ui.button></div>
    </form>
</x-ui.create-dialog>
@endsection
