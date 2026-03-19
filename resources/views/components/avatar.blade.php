@props(['name' => 'User', 'image' => null])

@php
    $initials = collect(explode(' ', $name))
        ->map(function ($segment) {
            return strtoupper(substr($segment, 0, 1));
        })
        ->take(2)
        ->join('');
@endphp

<div {{ $attributes->merge(['class' => 'w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0']) }}>
    @if($image)
        <img src="{{ $image }}" alt="{{ $name }}" class="w-full h-full object-cover">
    @else
        <span class="text-sm font-bold text-slate-600 tracking-wide">{{ $initials }}</span>
    @endif
</div>
