@props([
    'name' => '',
    'value' => '',
    'label' => '',
    'id' => null,
    'checked' => false,
    'disabled' => false,
])

@php
    $inputId = $id ?? ($name . '_' . $value);
@endphp

<label for="{{ $inputId }}" class="inline-flex items-center gap-2.5 cursor-pointer select-none group {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
    <div class="relative flex items-center justify-center">
        <input 
            type="checkbox" 
            id="{{ $inputId }}"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ $checked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'peer sr-only']) }}
        >
        <div class="w-[18px] h-[18px] rounded-[5px] border-2 border-slate-300 bg-white transition-all duration-200 ease-out
                    peer-checked:border-[#130F26] peer-checked:bg-[#130F26]
                    peer-focus-visible:ring-2 peer-focus-visible:ring-[#130F26]/30 peer-focus-visible:ring-offset-1
                    peer-disabled:opacity-50 peer-disabled:cursor-not-allowed
                    group-hover:border-slate-400 peer-checked:group-hover:border-[#130F26]
                    peer-checked:shadow-sm"></div>
        <svg class="absolute w-[11px] h-[11px] text-white pointer-events-none opacity-0 scale-50 transition-all duration-200 ease-out peer-checked:opacity-100 peer-checked:scale-100" 
             viewBox="0 0 12 10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1.5 5.5L4.5 8.5L10.5 1.5"/>
        </svg>
    </div>
    @if($label)
        <span class="text-sm text-slate-700 group-hover:text-slate-900 transition-colors">{{ $label }}</span>
    @endif
</label>
