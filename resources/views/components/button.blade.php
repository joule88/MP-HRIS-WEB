@props(['type' => 'submit', 'variant' => 'primary', 'href' => null])

@php
    $baseClass = "px-6 py-2.5 rounded-xl font-medium transition-all duration-300 inline-flex items-center justify-center cursor-pointer text-sm md:text-base hover:-translate-y-0.5 active:scale-95 active:translate-y-0";
    
    $variants = [
        'primary' => "bg-gradient-to-r from-[#130F26] to-[#2a244a] text-white hover:shadow-[var(--shadow-glow)] border border-transparent",        
        'secondary' => "bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:shadow-sm",
        
        'danger' => "bg-gradient-to-r from-red-600 to-red-500 text-white hover:shadow-[0_4px_15px_-3px_rgba(220,38,38,0.4)] border border-transparent",
    ];

    $class = $baseClass . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $class]) }}>
        {{ $slot }}
    </button>
@endif
