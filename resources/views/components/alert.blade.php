@props(['type' => 'info', 'title' => null])

@php
    $variants = [
        'info' => 'bg-blue-50 border-blue-100 text-blue-800 icon-blue-600',
        
        'danger' => 'bg-red-50 border-red-100 text-red-800 icon-red-600',
        
        'success' => 'bg-emerald-50 border-emerald-100 text-emerald-800 icon-emerald-600',
        
        'warning' => 'bg-orange-50 border-orange-100 text-orange-800 icon-orange-600',
    ];

    $style = $variants[$type] ?? $variants['info'];
    
@endphp

<div {{ $attributes->merge(['class' => "p-4 rounded-lg border flex items-start gap-3 $style"]) }}>
    <div class="shrink-0 mt-0.5">
        @if($type == 'danger')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        @elseif($type == 'success')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        @else
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        @endif
    </div>

    <div>
        @if($title)
            <h4 class="text-sm font-bold mb-1">{{ $title }}</h4>
        @endif
        <div class="text-sm opacity-90">
            {{ $slot }}
        </div>
    </div>
</div>
