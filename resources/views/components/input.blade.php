@props(['label' => null, 'name', 'type' => 'text', 'placeholder' => '', 'value' => '', 'disabled' => false])

@php
    $id = $attributes->get('id', $name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full mb-4']) }}>
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-slate-700 mb-2">
            {{ $label }}
        </label>
    @endif
    
    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        id="{{ $id }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $disabled ? 'disabled' : '' }}
        {!! $attributes->except(['id', 'name', 'type', 'value', 'placeholder', 'class'])->merge([
            'class' => 'w-full block px-4 py-2 border border-slate-200 bg-slate-50 rounded-xl focus:bg-white focus:ring-4 focus:ring-[#130F26]/10 focus:border-[#130F26] outline-none transition-all duration-300 placeholder-slate-400 disabled:bg-slate-100 disabled:text-slate-500 h-11'
        ]) !!}
    >
    
    @error($name)
        <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ $message }}
        </p>
    @enderror
</div>
