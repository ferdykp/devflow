<x-ui.panel id="section-scope"><h2 class="mb-3 font-semibold">Penjaga Cakupan</h2><form method="POST" action="{{ route('scope.baseline',$project) }}" class="space-y-3">@csrf<label class="block text-sm font-medium">Termasuk cakupan</label><textarea name="in_scope" rows="2" class="w-full rounded-lg border border-[#dededb] p-3 text-sm">{{ $project->baseline?->in_scope }}</textarea><label class="block text-sm font-medium">Di luar cakupan</label><textarea name="out_of_scope" rows="2" class="w-full rounded-lg border border-[#dededb] p-3 text-sm">{{ $project->baseline?->out_of_scope }}</textarea><x-ui.button>Simpan batasan</x-ui.button></form><form method="POST" action="{{ route('scope.review',$project) }}" class="mt-5 space-y-3 border-t border-zinc-100 pt-4">@csrf<x-ui.field name="title" label="Judul usulan perubahan" required/><x-ui.button>Ajukan peninjauan</x-ui.button></form>
<div class="mt-5 space-y-4" aria-label="Riwayat peninjauan cakupan">
@forelse($scopeReviews as $review)
<div class="border-t border-zinc-200 pt-4">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <p class="text-sm font-semibold">{{ $review->title }}</p>
            @if($review->description)<p class="mt-1 whitespace-pre-wrap text-sm text-[#60615e]">{{ $review->description }}</p>@endif
        </div>
        <span class="rounded-md bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700">{{ $review->status === 'pending' ? 'Perlu ditinjau' : 'Selesai ditinjau' }}</span>
    </div>
    @if($review->status === 'pending')
    <form method="POST" action="{{ route('scope.decide', [$project, $review]) }}" class="mt-3 space-y-3">
        @csrf
        <div>
            <label for="classification-{{ $review->id }}" class="mb-1 block text-xs font-medium text-zinc-700">Penilaian</label>
            <select id="classification-{{ $review->id }}" name="classification" required class="w-full rounded-lg border border-[#dededb] bg-white p-2.5 text-sm">
                <option value="not_assessable">Belum bisa dinilai — cakupan belum disetujui</option>
                <option value="in_scope">Masih dalam cakupan saat ini</option>
                <option value="out_of_scope">Di luar cakupan saat ini</option>
                <option value="uncertain">Belum jelas — perlu dibahas</option>
            </select>
        </div>
        <div>
            <label for="evidence-{{ $review->id }}" class="mb-1 block text-xs font-medium text-zinc-700">Bukti (opsional)</label>
            <textarea id="evidence-{{ $review->id }}" name="evidence" rows="2" maxlength="5000" placeholder="Bagian PRD, kebutuhan, atau catatan terkait" class="w-full rounded-lg border border-[#dededb] p-2.5 text-sm"></textarea>
        </div>
        <div>
            <label for="reason-{{ $review->id }}" class="mb-1 block text-xs font-medium text-zinc-700">Alasan keputusan</label>
            <textarea id="reason-{{ $review->id }}" name="reason" rows="2" maxlength="3000" required placeholder="Jelaskan alasan keputusan ini" class="w-full rounded-lg border border-[#dededb] p-2.5 text-sm"></textarea>
        </div>
        <div>
            <label for="bucket-{{ $review->id }}" class="mb-1 block text-xs font-medium text-zinc-700">Kategori ide (jika disimpan untuk nanti)</label>
            <select id="bucket-{{ $review->id }}" name="bucket" class="w-full rounded-lg border border-[#dededb] bg-white p-2.5 text-sm">
                <option value="future">Nanti</option><option value="research">Riset</option><option value="someday">Suatu saat nanti</option>
            </select>
        </div>
        <p class="text-xs text-[#747571]">Menyetujui usulan tidak otomatis mengubah PRD atau cakupan yang sudah disetujui. Buat versi PRD baru jika perlu perubahan.</p>
        <div class="flex flex-wrap gap-2">
            <button type="submit" name="decision" value="approve_proposal" class="rounded-lg bg-zinc-950 px-3 py-2 text-xs font-semibold text-white hover:bg-zinc-800">Setujui untuk perencanaan</button>
            <button type="submit" name="decision" value="move_to_backlog" class="rounded-lg border border-zinc-300 px-3 py-2 text-xs font-semibold hover:bg-zinc-50">Simpan untuk nanti</button>
            <button type="submit" name="decision" value="reject" class="rounded-lg border border-zinc-300 px-3 py-2 text-xs font-semibold hover:bg-zinc-50">Tolak</button>
        </div>
    </form>
    @else
    @php($decision = $review->decisions->first())
    <p class="mt-2 text-xs text-[#60615e]">{{ ucfirst(str_replace('_', ' ', $review->classification)) }} · {{ $decision ? ucfirst(str_replace('_', ' ', $decision->decision)) : 'Selesai' }}</p>
    @if($decision?->reason)<p class="mt-1 whitespace-pre-wrap text-xs text-[#60615e]">{{ $decision->reason }}</p>@endif
    @endif
</div>
@empty
<p class="mt-4 text-sm text-[#747571]">Belum ada peninjauan cakupan.</p>
@endforelse
</div></x-ui.panel>
