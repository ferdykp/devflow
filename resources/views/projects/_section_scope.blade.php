<x-ui.panel id="section-scope" class="space-y-6">
    <section>
        <h2 class="font-semibold">Penjaga Cakupan</h2>
        <p class="mt-1 text-sm text-zinc-600">Simpan baseline manual atau tautkan ke PRD yang disetujui. Baseline ini membantu tim menilai usulan, tetapi tidak memutuskan perubahan secara otomatis.</p>
        <form method="POST" action="{{ route('scope.baseline', $project) }}" class="mt-4 grid gap-4 md:grid-cols-2">
            @csrf
            <label class="block text-sm font-medium">Termasuk cakupan<textarea name="in_scope" rows="3" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm">{{ $project->baseline?->in_scope }}</textarea></label>
            <label class="block text-sm font-medium">Di luar cakupan<textarea name="out_of_scope" rows="3" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm">{{ $project->baseline?->out_of_scope }}</textarea></label>
            <label class="block text-sm font-medium">Constraints<textarea name="constraints" rows="2" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm">{{ $project->baseline?->constraints }}</textarea></label>
            <label class="block text-sm font-medium">Assumptions<textarea name="assumptions" rows="2" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm">{{ $project->baseline?->assumptions }}</textarea></label>
            <label class="block text-sm font-medium md:col-span-2">Kriteria keberhasilan<textarea name="success_criteria" rows="2" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm">{{ $project->baseline?->success_criteria }}</textarea></label>
            <label class="block text-sm font-medium md:col-span-2">Definition of Done<textarea name="definition_of_done" rows="2" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm">{{ $project->baseline?->definition_of_done }}</textarea></label>
            <label class="block text-sm font-medium md:col-span-2">Versi PRD yang disetujui (opsional)
                <select name="prd_version_id" class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm">
                    <option value="">Baseline manual tanpa PRD</option>
                    @foreach($approvedPrdVersions as $version)
                        <option value="{{ $version->id }}" @selected($project->baseline?->prd_version_id === $version->id)>{{ $version->document->title }} · v{{ $version->version_number }}</option>
                    @endforeach
                </select>
            </label>
            <div class="md:col-span-2"><x-ui.button>Simpan baseline</x-ui.button></div>
        </form>
        @if($project->baseline?->prdVersion)
            <p class="mt-2 text-xs text-zinc-500">Acuan tersimpan: {{ $project->baseline->prdVersion->document->title }} · v{{ $project->baseline->prdVersion->version_number }}. Histori sebelum acuan ini tetap tidak diketahui.</p>
        @endif
    </section>

    <section class="border-t border-zinc-200 pt-5">
        <h3 class="font-semibold">Tinjau usulan scope</h3>
        <form method="POST" action="{{ route('scope.review', $project) }}" class="mt-3 grid gap-3 sm:grid-cols-2">
            @csrf
            <x-ui.field id="scope-review-title" name="title" label="Judul usulan" required />
            <label class="block text-sm font-medium">Konteks tambahan<textarea name="description" rows="2" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm"></textarea></label>
            <div class="sm:col-span-2"><x-ui.button>Ajukan peninjauan</x-ui.button></div>
        </form>
        @if(!$project->baseline && $approvedPrdVersions->isEmpty())
            <p class="mt-2 text-xs text-amber-700">Belum ada baseline. Peninjauan tetap dapat dicatat, tetapi klasifikasi pasti akan ditolak sampai ada acuan.</p>
        @endif

        <div class="mt-5 space-y-4" aria-label="Riwayat peninjauan cakupan">
            @forelse($scopeReviews as $review)
                <article class="border-t border-zinc-200 pt-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold">{{ $review->title }}</p>
                            @if($review->description)<p class="mt-1 whitespace-pre-wrap text-sm text-zinc-600">{{ $review->description }}</p>@endif
                            <p class="mt-1 text-xs text-zinc-500">Acuan: {{ $review->prdVersion ? $review->prdVersion->document->title.' · v'.$review->prdVersion->version_number : 'baseline manual / belum ditautkan' }}</p>
                        </div>
                        <span class="rounded-md bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700">{{ $review->status === 'pending' ? 'Perlu ditinjau' : 'Selesai ditinjau' }}</span>
                    </div>
                    @if($review->status === 'pending')
                        <form method="POST" action="{{ route('scope.decide', [$project, $review]) }}" class="mt-3 space-y-3">
                            @csrf
                            <label class="block text-xs font-medium text-zinc-700">Klasifikasi
                                <select name="classification" required class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm">
                                    <option value="not_assessable">Belum bisa dinilai</option>
                                    <option value="in_scope">Dalam cakupan</option>
                                    <option value="potential_out_of_scope">Berpotensi di luar cakupan</option>
                                    <option value="focus_misalignment">Tidak selaras dengan fokus proyek</option>
                                    <option value="unplanned">Belum direncanakan</option>
                                    <option value="uncertain">Belum jelas</option>
                                    <option value="out_of_scope">Di luar cakupan (klasifikasi historis)</option>
                                </select>
                            </label>
                            <label class="block text-xs font-medium text-zinc-700">Bukti atau sumber<textarea name="evidence" rows="2" maxlength="5000" placeholder="Bagian PRD, kebutuhan, atau catatan terkait" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm"></textarea></label>
                            <label class="block text-xs font-medium text-zinc-700">Alasan keputusan<textarea name="reason" rows="2" maxlength="3000" required placeholder="Jelaskan alasan keputusan ini" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm"></textarea></label>
                            <label class="block text-xs font-medium text-zinc-700">Kategori ide jika disimpan untuk nanti
                                <select name="bucket" class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm"><option value="future">Nanti</option><option value="research">Riset</option><option value="someday">Suatu saat nanti</option></select>
                            </label>
                            <p class="text-xs text-zinc-500">Menyetujui usulan hanya mengizinkan perencanaan. Ini tidak mengubah PRD, baseline, task, atau progress.</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="submit" name="decision" value="approve_proposal" class="min-h-10 rounded-lg bg-zinc-950 px-3 text-xs font-semibold text-white">Setujui untuk perencanaan</button>
                                <button type="submit" name="decision" value="move_to_backlog" class="min-h-10 rounded-lg border border-zinc-300 px-3 text-xs font-semibold">Simpan untuk nanti</button>
                                <button type="submit" name="decision" value="reject" class="min-h-10 rounded-lg border border-zinc-300 px-3 text-xs font-semibold">Tolak</button>
                            </div>
                        </form>
                    @else
                        @php($decision = $review->decisions->first())
                        <p class="mt-2 text-xs text-zinc-600">{{ ['in_scope' => 'Dalam cakupan', 'out_of_scope' => 'Di luar cakupan', 'potential_out_of_scope' => 'Berpotensi di luar cakupan', 'focus_misalignment' => 'Tidak selaras dengan fokus', 'unplanned' => 'Belum direncanakan', 'uncertain' => 'Belum jelas', 'not_assessable' => 'Belum bisa dinilai'][$review->classification] ?? $review->classification }} · {{ $decision ? (['approve_proposal' => 'Disetujui untuk perencanaan', 'reject' => 'Ditolak', 'move_to_backlog' => 'Disimpan untuk nanti'][$decision->decision] ?? $decision->decision) : 'Selesai' }}</p>
                        @if($decision?->reason)<p class="mt-1 whitespace-pre-wrap text-xs text-zinc-600">{{ $decision->reason }}</p>@endif
                        @if($decision?->evidence)<p class="mt-1 whitespace-pre-wrap text-xs text-zinc-500">Bukti: {{ $decision->evidence }}</p>@endif
                    @endif
                </article>
            @empty
                <p class="border-t border-zinc-200 pt-4 text-sm text-zinc-500">Belum ada peninjauan cakupan.</p>
            @endforelse
        </div>
    </section>

    <section class="border-t border-zinc-200 pt-5">
        <h3 class="font-semibold">Change Requests</h3>
        <p class="mt-1 text-sm text-zinc-600">Keputusan Change Request, pembuatan draf PRD, dan persetujuan versi PRD adalah tiga tindakan terpisah.</p>
        <form method="POST" action="{{ route('change-requests.store', $project) }}" class="mt-3 space-y-3">
            @csrf
            <label class="block text-sm font-medium">Dari peninjauan yang disetujui (opsional)
                <select name="scope_review_id" class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm">
                    <option value="">Change Request mandiri</option>
                    @foreach($scopeReviews->filter(fn($review) => $review->status === 'resolved' && $review->decisions->contains('decision', 'approve_proposal')) as $review)
                        <option value="{{ $review->id }}">{{ $review->title }}</option>
                    @endforeach
                </select>
            </label>
            <x-ui.field id="change-request-title" name="title" label="Judul Change Request" required />
            <label class="block text-sm font-medium">Perubahan yang diminta<textarea name="description" rows="3" maxlength="5000" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm"></textarea></label>
            <x-ui.button>Catat Change Request</x-ui.button>
        </form>

        <div class="mt-5 space-y-4">
            @forelse($changeRequests as $changeRequest)
                <article class="border-t border-zinc-200 pt-4">
                    <div class="flex flex-wrap items-center justify-between gap-2"><h4 class="text-sm font-semibold">{{ $changeRequest->title }}</h4><span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs">{{ ['proposed' => 'Diusulkan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'deferred' => 'Ditunda'][$changeRequest->status] ?? $changeRequest->status }}</span></div>
                    @if($changeRequest->description)<p class="mt-1 whitespace-pre-wrap text-sm text-zinc-600">{{ $changeRequest->description }}</p>@endif
                    <p class="mt-1 text-xs text-zinc-500">Diajukan oleh {{ $changeRequest->requester->name }} · Sumber {{ $changeRequest->sourcePrdVersion ? $changeRequest->sourcePrdVersion->document->title.' v'.$changeRequest->sourcePrdVersion->version_number : 'baseline manual / belum ditautkan' }}</p>
                    @if($changeRequest->decision_reason)<p class="mt-2 text-sm text-zinc-600">{{ $changeRequest->decision_reason }}</p>@endif
                    @if($changeRequest->status === 'proposed')
                        <form method="POST" action="{{ route('change-requests.decide', [$project, $changeRequest]) }}" class="mt-3 grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto]">
                            @csrf @method('PATCH')
                            <div class="grid gap-2 sm:grid-cols-2">
                                <select name="status" required class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm"><option value="approved">Setujui perubahan</option><option value="rejected">Tolak</option><option value="deferred">Tunda</option></select>
                                <input name="decision_reason" required maxlength="3000" placeholder="Alasan keputusan" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm">
                            </div>
                            <button class="min-h-10 rounded-lg bg-zinc-950 px-3 text-xs font-semibold text-white">Simpan keputusan</button>
                        </form>
                    @elseif($changeRequest->status === 'approved' && !$changeRequest->draft_prd_version_id)
                        <form method="POST" action="{{ route('change-requests.prd-draft', [$project, $changeRequest]) }}" class="mt-3 space-y-2">
                            @csrf
                            <label class="block text-xs font-medium">Isi draf revisi PRD<textarea name="content_markdown" rows="5" maxlength="200000" required class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 font-mono text-xs" placeholder="# Revisi PRD&#10;Tulis konten lengkap versi baru..."></textarea></label>
                            <button class="min-h-10 rounded-lg border border-zinc-300 px-3 text-xs font-semibold">Buat draf PRD terpisah</button>
                        </form>
                    @elseif($changeRequest->draftPrdVersion)
                        <p class="mt-2 text-xs text-zinc-600">Draf PRD v{{ $changeRequest->draftPrdVersion->version_number }} dibuat · status {{ $changeRequest->draftPrdVersion->status }}. Persetujuan PRD belum dilakukan.</p>
                    @endif
                </article>
            @empty
                <p class="border-t border-zinc-200 pt-4 text-sm text-zinc-500">Belum ada Change Request.</p>
            @endforelse
        </div>
    </section>
</x-ui.panel>
