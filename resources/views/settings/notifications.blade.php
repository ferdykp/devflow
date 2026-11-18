@extends('layouts.app')
@section('title', 'Notifikasi')
@section('page-heading', 'Pusat notifikasi')
@section('content')
<x-ui.page-heading title="Pusat notifikasi." description="Pembaruan komentar tugas, scope, dan Change Request untuk proyek yang kamu ikuti."><a href="{{ route('settings.index') }}" class="text-sm font-medium text-blue-700">Atur pengingat</a></x-ui.page-heading>

<x-ui.panel>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-zinc-600"><span class="font-semibold text-zinc-900">{{ $unreadCount }}</span> belum dibaca</p>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">@csrf @method('PATCH')<button class="min-h-10 rounded-lg border border-zinc-300 px-3 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Tandai semua dibaca</button></form>
        @endif
    </div>

    @forelse($notifications as $notification)
        @php($data = $notification->data)
        <article class="flex flex-wrap items-start justify-between gap-3 border-t border-zinc-100 py-4 {{ $notification->read_at ? '' : 'bg-blue-50/50' }}">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-zinc-900">{{ data_get($data, 'title', 'Pembaruan proyek') }} @unless($notification->read_at)<span class="ml-1 inline-block size-2 rounded-full bg-blue-600" aria-label="Belum dibaca"></span>@endunless</p>
                <p class="mt-1 whitespace-pre-wrap text-sm leading-6 text-zinc-600">{{ data_get($data, 'message', '') }}</p>
                <p class="mt-1 text-xs text-zinc-400">{{ $notification->created_at->timezone(auth()->user()->timezone ?: config('app.timezone'))->format('d M Y, H:i') }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf @method('PATCH')<button class="min-h-10 rounded-lg px-3 text-sm font-semibold text-blue-700 hover:bg-blue-50">Buka</button></form>
                <form method="POST" action="{{ route('notifications.dismiss', $notification->id) }}">@csrf @method('DELETE')<button aria-label="Hapus notifikasi" class="grid size-10 place-items-center rounded-lg text-zinc-500 hover:bg-zinc-100"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></form>
            </div>
        </article>
    @empty
        <div class="border-t border-zinc-100 py-10 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-zinc-100 text-xl text-zinc-500"><i class="fa-regular fa-bell-slash" aria-hidden="true"></i></span>
            <h2 class="mt-4 font-semibold">Belum ada notifikasi</h2>
            <p class="mt-1 text-sm leading-6 text-zinc-500">Pembaruan proyek baru akan muncul di sini. Pengingat tugas dan rutinitas terjadwal belum dikirim otomatis.</p>
            <a href="{{ route('projects.index') }}" class="mt-4 inline-flex min-h-10 items-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white">Buka proyek</a>
        </div>
    @endforelse
    @if($notifications->hasPages())<div class="mt-4 border-t border-zinc-100 pt-4">{{ $notifications->links() }}</div>@endif
</x-ui.panel>
@endsection
