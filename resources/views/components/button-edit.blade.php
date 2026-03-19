@props(['href' => null])

@if($href)
    
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'p-2 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg transition border border-amber-200 group inline-flex items-center justify-center']) }} title="Edit">
        <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
        </svg>
    </a>
@else
    
    <button {{ $attributes->merge(['class' => 'p-2 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg transition border border-amber-200 group']) }} title="Edit">
        <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
        </svg>
    </button>
@endif
