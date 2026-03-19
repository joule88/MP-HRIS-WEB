@props(['href' => null, 'type' => 'button', 'color' => 'blue'])

@php
    $colors = [
        'blue'   => 'bg-blue-50 text-blue-600 hover:bg-blue-100 border-blue-200',
        'yellow' => 'bg-yellow-50 text-yellow-600 hover:bg-yellow-100 border-yellow-200',
        'red'    => 'bg-red-50 text-red-600 hover:bg-red-100 border-red-200',
        'slate'  => 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-slate-200',
    ];

    $style = $colors[$color] ?? $colors['blue'];
    $baseClass = "p-2 rounded-lg transition border flex items-center justify-center";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$baseClass $style"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "$baseClass $style"]) }}>
        {{ $slot }}
    </button>
@endif
