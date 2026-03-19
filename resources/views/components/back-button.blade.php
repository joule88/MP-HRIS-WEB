@props(['href'])

<a href="{{ $href }}" 
   {{ $attributes->merge(['class' => 'btn-back inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all shadow-sm cursor-pointer']) }}>
    
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
    </svg>

    {{ $slot->isEmpty() ? 'Kembali' : $slot }}
</a>
