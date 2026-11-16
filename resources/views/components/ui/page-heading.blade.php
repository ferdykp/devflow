@props(['title', 'description' => null, 'eyebrow' => null])
<header {{ $attributes->class(['df-page-heading df-panel mb-6 flex flex-col items-stretch justify-between gap-4 rounded-2xl border p-5 xl:flex-row xl:items-center xl:p-6']) }}>
    <div class="w-full min-w-0 xl:flex-1">
        @if($eyebrow)<p class="mb-2 text-[11px] font-bold uppercase tracking-[.14em] text-[var(--df-secondary)]">{{ $eyebrow }}</p>@endif
        <h1 class="df-page-title min-w-0 max-w-full [overflow-wrap:anywhere]">{{ $title }}</h1>
        @if($description)<p class="df-page-description mt-2 max-w-2xl [overflow-wrap:anywhere]">{{ $description }}</p>@endif
    </div>
    @if($slot->isNotEmpty())<div class="flex w-full min-w-0 flex-wrap items-center justify-start gap-2 xl:w-auto xl:shrink-0 xl:justify-end">{{ $slot }}</div>@endif
</header>
