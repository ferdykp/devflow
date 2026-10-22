@extends('layouts.app')
@section('title', 'Anggota ruang kerja')
@section('page-heading', 'Anggota ruang kerja')
@section('content')
<x-ui.page-heading :title="$workspace->name" description="Semua anggota dapat mengelola proyek, dokumen, dan tugas. Pemilik mengelola undangan dan keanggotaan." eyebrow="Ruang kerja">
    <x-ui.button :href="route('projects.index')" variant="secondary">Kembali ke proyek</x-ui.button>
</x-ui.page-heading>
<div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
    <x-ui.panel>
        <h2 class="text-lg font-semibold">Anggota <span class="text-sm font-normal text-zinc-500">{{ $participants->count() }}</span></h2>
        <div class="mt-5 divide-y divide-zinc-100">
            @foreach($participants as $participant)
                <div class="flex flex-wrap items-center justify-between gap-3 py-4">
                    <div class="min-w-0"><p class="break-words font-medium">{{ $participant->name }} @if($participant->id === auth()->id())<span class="text-sm font-normal text-zinc-500">(kamu)</span>@endif</p><p class="mt-1 break-all text-sm text-zinc-500">{{ $participant->email }}</p><p class="mt-1 text-xs text-zinc-500">{{ $workspace->user_id === $participant->id ? 'Pemilik' : 'Anggota' }}</p></div>
                    @if($participant->id !== $workspace->user_id && ($workspace->user_id === auth()->id() || $participant->id === auth()->id()))
                        <form action="{{ route('workspaces.members.destroy', [$workspace, $participant]) }}" method="POST" data-confirm="Cabut akses anggota ini ke seluruh proyek dalam ruang kerja?">@csrf @method('DELETE')<x-ui.button variant="danger">{{ $participant->id === auth()->id() ? 'Keluar ruang kerja' : 'Keluarkan anggota' }}</x-ui.button></form>
                    @endif
                </div>
            @endforeach
        </div>
    </x-ui.panel>
    @if($workspace->user_id === auth()->id())
        <div class="min-w-0 space-y-6">
            <x-ui.panel>
                <h2 class="text-lg font-semibold">Undang anggota</h2>
                <p class="mt-2 text-sm leading-6 text-zinc-500">Buat tautan untuk satu alamat email. Calon anggota perlu masuk atau mendaftar dengan email tersebut. Berlaku tujuh hari.</p>
                <form action="{{ route('workspaces.invitations.store', $workspace) }}" method="POST" class="mt-5 space-y-4">@csrf
                    <x-ui.field name="email" label="Email anggota" type="email" required maxlength="255" placeholder="teman@contoh.com" />
                    <x-ui.button class="w-full">Buat undangan</x-ui.button>
                </form>
                @if(session('invitation_url'))
                    <div class="mt-5 rounded-lg border border-blue-100 bg-blue-50 p-4">
                        <label for="invitation-url" class="block text-sm font-medium text-blue-900">Tautan undangan</label>
                        <input id="invitation-url" readonly value="{{ session('invitation_url') }}" class="mt-2 w-full min-w-0 rounded-lg border border-blue-200 bg-white p-3 text-sm" onclick="this.select()">
                        <p class="mt-2 text-xs leading-5 text-blue-800">Salin tautan ini dan bagikan langsung. DevFlow tidak mengirim email undangan.</p>
                    </div>
                @endif
            </x-ui.panel>
            @if($invitations->isNotEmpty())
                <x-ui.panel><h2 class="font-semibold">Menunggu bergabung</h2><div class="mt-3 divide-y divide-zinc-100">
                    @foreach($invitations as $invitation)
                        <div class="py-3"><p class="break-all text-sm font-medium">{{ $invitation->email }}</p><p class="mt-1 text-xs text-zinc-500">Berlaku hingga {{ $invitation->expires_at->format('d M Y') }}</p><form class="mt-3" action="{{ route('workspaces.invitations.destroy', [$workspace, $invitation]) }}" method="POST" data-confirm="Batalkan undangan ini?">@csrf @method('DELETE')<x-ui.button variant="secondary">Batalkan undangan</x-ui.button></form></div>
                    @endforeach
                </div></x-ui.panel>
            @endif
        </div>
    @endif
</div>
@endsection
