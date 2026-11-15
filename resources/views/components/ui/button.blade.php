@props(['type' => 'submit', 'href' => null, 'variant' => 'primary'])
@php
    $base = 'df-button inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold leading-5 transition-colors duration-150 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-50 motion-reduce:transition-none';
    $tone = match ($variant) {
        'secondary' => 'df-button-secondary shadow-xs',
        'danger' => 'border border-red-200 bg-red-50 text-red-700 hover:border-red-300 hover:bg-red-100',
        'ghost' => 'df-button-ghost',
        default => 'df-button-primary shadow-sm',
    };
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->class([$base, $tone]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$base, $tone]) }}>{{ $slot }}</button>
@endif
