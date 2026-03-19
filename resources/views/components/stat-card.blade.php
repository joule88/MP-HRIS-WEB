@props(['label'=>'', 'value', 'unit' => '', 'color' => 'blue'])

@php
    $colors = [
        'indigo'  => 'bg-indigo-50 text-indigo-600',
        'purple'  => 'bg-purple-50 text-purple-600',
        'blue'    => 'bg-blue-50 text-blue-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'orange'  => 'bg-orange-50 text-orange-600',
        'red'     => 'bg-red-50 text-red-600',
    ];
    
    $colorClass = $colors[$color] ?? $colors['blue'];
@endphp

<div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[var(--shadow-card)] hover:shadow-[var(--shadow-soft)] hover:-translate-y-1 transition-all duration-300 flex flex-col justify-center relative overflow-hidden group">
    
    <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-current to-transparent opacity-[0.03] rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500 {{ str_replace('bg-', 'text-', explode(' ', $colorClass)[0]) }}"></div>
    
    <div class="flex items-center gap-4 mb-4 relative z-10">
        <div class="p-3 rounded-2xl {{ $colorClass }} shadow-sm">
            {{ $slot }}
        </div>
        <span class="text-sm font-semibold text-slate-500 uppercase tracking-wider">{{ $label }}</span>
    </div>
    <div class="relative z-10">
        <h3 class="text-3xl md:text-4xl font-extrabold text-slate-800 tracking-tight">
            {{ $value }} 
            @if($unit)
                <span class="text-base font-semibold text-slate-400 ml-1">{{ $unit }}</span>
            @endif
        </h3>
    </div>
</div>
