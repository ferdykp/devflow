@props(['type', 'title', 'description' => null, 'errors' => false, 'size' => 'wide'])
<dialog data-create-dialog data-create-type="{{ $type }}" @if($errors) data-create-errors @endif aria-labelledby="create-{{ $type }}-title" class="fixed left-1/2 top-1/2 m-0 max-h-[calc(100dvh-24px)] {{ $size === 'compact' ? 'w-[min(520px,calc(100vw-24px))]' : 'w-[min(920px,calc(100vw-24px))]' }} -translate-x-1/2 -translate-y-1/2 overflow-hidden rounded-2xl border border-zinc-200 bg-white p-0 shadow-2xl">
    <div class="flex max-h-[calc(100dvh-24px)] flex-col">
        <header class="flex items-start justify-between gap-4 border-b border-zinc-100 bg-white px-5 py-4 sm:px-7">
            <div class="min-w-0"><h2 id="create-{{ $type }}-title" class="text-lg font-semibold text-zinc-950">{{ $title }}</h2>@if($description)<p class="mt-1 text-sm text-zinc-600">{{ $description }}</p>@endif</div>
            <button type="button" data-create-close aria-label="Tutup" class="grid size-10 shrink-0 place-items-center rounded-lg text-zinc-500 hover:bg-zinc-100"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </header>
        <div class="overflow-y-auto overscroll-contain p-5 sm:p-7">{{ $slot }}</div>
    </div>
</dialog>
