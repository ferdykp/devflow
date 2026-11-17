<x-ui.panel>
    <h2 class="mb-3 font-semibold">Aktivitas GitHub</h2>
    <p class="mb-4 text-xs text-[#747571]">Hubungkan repositori publik secara langsung, atau gunakan GitHub App untuk repositori privat. Sinkronisasi hanya membaca data.</p>
    <a href="{{ route('github.app.authorize', $project) }}" class="mb-4 inline-flex min-h-11 items-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 px-4 text-sm font-semibold text-white transition hover:border-black hover:bg-black focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-900">
        <i class="fa-brands fa-github" aria-hidden="true"></i>Hubungkan GitHub App
    </a>
    <form class="space-y-3 rounded-xl border border-zinc-200 bg-zinc-50/70 p-4" method="POST" action="{{ route('github.link', $project) }}">
        @csrf
        <x-ui.field name="full_name" label="Repositori publik" placeholder="owner/repository" required />
        <x-ui.button><i class="fa-solid fa-link" aria-hidden="true"></i>Hubungkan repositori</x-ui.button>
    </form>
    <div class="mt-4 space-y-3">
        @foreach($repositories as $repository)
            <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4">
                <p class="break-all text-sm font-semibold">{{ $repository->full_name }}</p>
                <p class="mt-1 text-xs text-[#747571]">Sinkronisasi terakhir: {{ $repository->last_synced_at?->diffForHumans() ?? 'Belum pernah' }}</p>
                <div class="mt-3 flex flex-wrap gap-2 border-t border-zinc-200 pt-3">
                    <form method="POST" action="{{ route('github.sync', [$project, $repository]) }}">
                        @csrf
                        <x-ui.button variant="secondary"><i class="fa-solid fa-rotate" aria-hidden="true"></i>Jadwalkan sinkronisasi</x-ui.button>
                    </form>
                    <form method="POST" action="{{ route('github.disconnect', [$project, $repository]) }}" data-confirm="Putuskan repositori ini dari proyek? Aktivitas yang sudah tercatat tetap tersimpan.">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger"><i class="fa-solid fa-link-slash" aria-hidden="true"></i>Putuskan repositori</x-ui.button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</x-ui.panel>
