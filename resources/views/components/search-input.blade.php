@props(['name' => 'search', 'placeholder' => 'Cari data...', 'value' => ''])

<div {{ $attributes->only('class')->merge(['class' => 'relative w-full lg:w-64']) }}>
    <input type="text" 
           name="{{ $name }}" 
           value="{{ $value }}" 
           placeholder="{{ $placeholder }}" 
           {{ $attributes->except('class')->merge(['class' => 'w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition h-10']) }}>
    
    <button type="submit" class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer bg-transparent border-none outline-none">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </button>
</div>
