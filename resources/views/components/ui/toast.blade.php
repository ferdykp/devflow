@props(['kind' => 'success'])
@php($isError = $kind === 'error')
<div role="{{ $isError ? 'alert' : 'status' }}" data-toast data-timeout="{{ $isError ? '0' : '4500' }}"
    class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-white p-4 text-sm leading-6 text-zinc-800 shadow-lg pointer-events-auto transition-[opacity,transform] duration-150 [&.is-hiding]:translate-x-2 [&.is-hiding]:opacity-0 motion-reduce:transition-none">
    <i aria-hidden="true" class="fa-solid mt-1 {{ $isError ? 'fa-circle-exclamation text-red-600' : 'fa-circle-check text-emerald-600' }}"></i>
    <div class="min-w-0 flex-1 break-words">{{ $slot }}</div>
    <button type="button" data-dismiss-toast aria-label="Tutup notifikasi" class="-my-2 -mr-2 grid size-11 shrink-0 place-items-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
</div>
