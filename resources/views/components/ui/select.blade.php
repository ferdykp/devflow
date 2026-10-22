@props(['name', 'label', 'options', 'value' => null, 'id' => null, 'required' => false])
@php
    $inputId = $id ?? str_replace(['[', ']'], ['-', ''], $name);
    $fieldKey = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
@endphp
<div class="min-w-0 space-y-2">
    <label for="{{ $inputId }}" class="block text-sm font-medium text-zinc-800">{{ $label }}</label>
    <select id="{{ $inputId }}" name="{{ $name }}" @required($required)
        @error($fieldKey) aria-invalid="true" aria-describedby="{{ $inputId }}-error" @enderror
        {{ $attributes->class(['w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 aria-invalid:border-red-500 sm:text-sm']) }}>
        @foreach($options as $key => $text)<option value="{{ $key }}" @selected((string) old($fieldKey, $value) === (string) $key)>{{ $text }}</option>@endforeach
    </select>
    @error($fieldKey)<p id="{{ $inputId }}-error" role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
</div>
