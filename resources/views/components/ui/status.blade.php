@props(['value'])
@php
    $tone = match ($value) {
        'active', 'in_progress' => 'border-blue-200 bg-blue-50 text-blue-700',
        'done' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'blocked' => 'border-red-200 bg-red-50 text-red-700',
        'in_review' => 'border-amber-200 bg-amber-50 text-amber-800',
        default => 'border-zinc-200 bg-zinc-50 text-zinc-600',
    };
@endphp
<span {{ $attributes->class(['inline-flex shrink-0 items-center gap-1.5 rounded-md border px-2 py-1 text-xs font-medium leading-4', $tone]) }}>
    <span aria-hidden="true" class="size-1.5 rounded-full bg-current"></span>
    {{ \App\Support\UiLabel::status($value) }}
</span>
