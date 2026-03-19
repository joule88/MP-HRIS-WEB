@props(['label' => null, 'name' => null, 'value' => ''])

@php
    $id = $attributes->get('id', $name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'relative w-full']) }}>
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-slate-700 mb-2">
            {{ $label }}
        </label>
    @endif
    
    <div class="relative group">
        <select 
            @if($name) name="{{ $name }}" @endif
            id="{{ $id }}"
            
            {{ $attributes->except(['class', 'id', 'name'])->merge(['class' => 'w-full pl-4 pr-12 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl text-sm appearance-none outline-none focus:ring-4 focus:ring-[#130F26]/10 focus:border-[#130F26] transition-all duration-300 cursor-pointer truncate block h-11']) }}
        >
            {{ $slot }}
        </select>
        
        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#130F26] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>
    
    @if($name)
        @error($name)
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    @endif
</div>
