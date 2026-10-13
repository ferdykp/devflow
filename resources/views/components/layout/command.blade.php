<dialog id="df-command" class="m-auto mt-[11vh] w-[min(93vw,580px)] max-h-[75vh] overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/60 backdrop:backdrop-blur-sm" aria-label="Pencarian cepat">
  <div class="flex h-16 items-center gap-3 border-b border-slate-100 px-5 text-slate-400">
    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
    <label class="sr-only" for="df-command-input">Cari halaman</label>
    <input id="df-command-input" type="search" autocomplete="off" placeholder="Cari halaman atau perintah..." data-command-input class="min-w-0 flex-1 border-0 bg-transparent text-sm text-slate-900 outline-none focus:ring-0" />
    <kbd class="shrink-0 rounded border border-slate-200 px-2 py-1 text-[10px]">ESC</kbd>
  </div>
  <div class="max-h-[50vh] space-y-1 overflow-y-auto p-3" data-command-results role="listbox" aria-label="Halaman tujuan">
    <p class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Buka halaman</p>
    <a role="option" href="{{ route('dashboard') }}" data-command-item class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700 data-[active]:bg-blue-50 data-[active]:text-blue-700"><i class="fa-solid fa-house w-5 text-center" aria-hidden="true"></i><span class="flex-1">Ringkasan</span><kbd class="text-xs text-slate-400">G O</kbd></a>
    <a role="option" href="{{ route('projects.index') }}" data-command-item class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700 data-[active]:bg-blue-50 data-[active]:text-blue-700"><i class="fa-regular fa-folder w-5 text-center" aria-hidden="true"></i><span class="flex-1">Proyek</span><kbd class="text-xs text-slate-400">G P</kbd></a>
    <a role="option" href="{{ route('focus.index') }}" data-command-item class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700 data-[active]:bg-blue-50 data-[active]:text-blue-700"><i class="fa-solid fa-bullseye w-5 text-center" aria-hidden="true"></i><span class="flex-1">Fokus Saya</span><kbd class="text-xs text-slate-400">G F</kbd></a>
    <p class="px-3 py-5 text-center text-sm text-slate-500" data-command-empty hidden>Tidak ada halaman yang cocok.</p>
  </div>
  <div class="border-t border-slate-100 px-5 py-3 text-xs text-slate-500">Ketik untuk mencari · ↑↓ untuk memilih · Enter untuk membuka</div>
</dialog>
