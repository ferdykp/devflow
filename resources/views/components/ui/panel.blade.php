@props(['padding' => true])
<section {{ $attributes->class(['min-w-0 rounded-xl border border-zinc-200 bg-white shadow-xs', 'p-5 sm:p-6' => $padding]) }}>{{ $slot }}</section>
