@props(['title', 'description' => null, 'eyebrow' => null])
<header {{ $attributes->class(['mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end']) }}>
    <div class="min-w-0">
        @if($eyebrow)<p class="mb-2 text-xs font-medium text-zinc-500">{{ $eyebrow }}</p>@endif
        <h1 class="break-words text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">{{ $title }}</h1>
        @if($description)<p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">{{ $description }}</p>@endif
    </div>
    @if($slot->isNotEmpty())<div class="flex shrink-0 flex-wrap items-center gap-2">{{ $slot }}</div>@endif
</header>
