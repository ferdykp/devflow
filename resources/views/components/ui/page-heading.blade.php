@props(['title', 'description' => null, 'eyebrow' => null])
<header {{ $attributes->class(['df-page-heading df-panel mb-6 flex flex-col items-start justify-between gap-4 rounded-2xl border p-5 sm:flex-row sm:items-center sm:p-6']) }}>
    <div class="min-w-0">
        @if($eyebrow)<p class="mb-2 text-[11px] font-bold uppercase tracking-[.14em] text-[var(--df-secondary)]">{{ $eyebrow }}</p>@endif
        <h1 class="df-page-title min-w-0 max-w-full [overflow-wrap:anywhere]">{{ $title }}</h1>
        @if($description)<p class="df-page-description mt-2 max-w-2xl [overflow-wrap:anywhere]">{{ $description }}</p>@endif
    </div>
    @if($slot->isNotEmpty())<div class="flex shrink-0 flex-wrap items-center gap-2">{{ $slot }}</div>@endif
</header>
