@props(['type' => 'submit', 'href' => null, 'variant' => 'primary'])
@php
    $base = 'inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium leading-5 transition-colors duration-150 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-50 motion-reduce:transition-none';
    $tone = match ($variant) {
        'secondary' => 'border border-zinc-300 bg-white text-zinc-700 shadow-xs hover:border-zinc-400 hover:bg-zinc-50',
        'danger' => 'border border-red-200 bg-red-50 text-red-700 hover:border-red-300 hover:bg-red-100',
        'ghost' => 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950',
        default => 'border border-blue-600 bg-blue-600 text-white shadow-xs hover:border-blue-700 hover:bg-blue-700',
    };
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->class([$base, $tone]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$base, $tone]) }}>{{ $slot }}</button>
@endif
