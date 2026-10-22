@props(['name', 'label', 'type' => 'text', 'value' => null, 'placeholder' => '', 'required' => false, 'id' => null])
@php
    $inputId = $id ?? str_replace(['[', ']'], ['-', ''], $name);
    $fieldKey = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
@endphp
<div class="space-y-2">
    <label for="{{ $inputId }}" class="block text-sm font-medium text-zinc-800">
        {{ $label }} @unless($required)<span class="font-normal text-zinc-500">(opsional)</span>@endunless
    </label>
    <input id="{{ $inputId }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($fieldKey, $value) }}"
        placeholder="{{ $placeholder }}" @required($required)
        @error($fieldKey) aria-invalid="true" aria-describedby="{{ $inputId }}-error" @enderror
        {{ $attributes->class(['df-input w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 aria-invalid:focus:ring-red-500/10 sm:text-sm motion-reduce:transition-none']) }}>
    @error($fieldKey)<p id="{{ $inputId }}-error" role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
</div>
