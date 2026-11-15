@props(['padding' => true])
<section {{ $attributes->class(['df-panel min-w-0 rounded-2xl border border-zinc-200 bg-white', 'p-5 sm:p-6' => $padding]) }}>{{ $slot }}</section>
