@props(['label' => null, 'name', 'value' => ''])

@php
    $id = $attributes->get('id', $name);
@endphp

<div class="space-y-1 mb-4">
    @if($label)
        <label for="{{ $id }}" class="text-sm font-semibold text-slate-700">
            {{ $label }}
        </label>
    @endif
    
    <textarea 
        name="{{ $name }}" 
        id="{{ $id }}"
        {{ $attributes->except(['class', 'id', 'name'])->merge(['class' => 'w-full px-4 py-3 bg-slate-50 focus:bg-white rounded-xl border border-slate-200 outline-none focus:ring-4 focus:ring-[#130F26]/10 focus:border-[#130F26] transition-all duration-300']) }}
    >{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
