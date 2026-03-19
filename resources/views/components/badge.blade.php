@props(['color' => 'gray'])

@php
    $colors = [
        'blue' => 'bg-blue-50 text-blue-700 border-blue-200 ring-blue-600/10',
        'green' => 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-600/10',
        'red' => 'bg-red-50 text-red-700 border-red-200 ring-red-600/10',
        'yellow' => 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-600/10',
        'purple' => 'bg-violet-50 text-violet-700 border-violet-200 ring-violet-600/10',
        'orange' => 'bg-orange-50 text-orange-700 border-orange-200 ring-orange-600/10',
        'cyan' => 'bg-cyan-50 text-cyan-700 border-cyan-200 ring-cyan-600/10',
        'gray' => 'bg-slate-50 text-slate-600 border-slate-200 ring-slate-600/10',
        'navy' => 'bg-[#130F26]/5 text-[#130F26] border-[#130F26]/10 ring-[#130F26]/10',
    ];

    $style = $colors[$color] ?? $colors['gray'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border ring-1 ring-inset $style"]) }}>
    {{ $slot }}
</span>
