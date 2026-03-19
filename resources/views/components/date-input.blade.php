@props(['label' => null, 'name', 'value' => ''])

<div class="space-y-1">
    @if($label)
        <label class="text-sm font-semibold text-slate-700">{{ $label }}</label>
    @endif
    
    <div class="relative">
        <style>
            /* Sembunyikan icon default browser */
            input[type="date"]::-webkit-calendar-picker-indicator,
            input[type="date"]::-webkit-inner-spin-button {
                display: none;
                -webkit-appearance: none;
            }
        </style>

        <input type="date" 
               name="{{ $name }}" 
               value="{{ old($name, $value) }}"
               onclick="try{this.showPicker()}catch(e){}"
               onfocus="try{this.showPicker()}catch(e){}"
               {{ $attributes->merge(['class' => 'w-full pl-4 pr-10 py-2.5 bg-white border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-[#130F26] focus:border-[#130F26] transition-all text-slate-600 cursor-pointer appearance-none']) }}
        >
        
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
    </div>

    @error($name)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
