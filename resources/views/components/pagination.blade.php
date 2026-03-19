@props(['paginator'])

@if($paginator->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
        <div class="flex items-center justify-between mt-2">
            
            <div class="text-sm text-slate-500 hidden sm:block">
                Menampilkan 
                <span class="font-bold text-slate-800">{{ $paginator->firstItem() ?? 0 }}</span> 
                sampai 
                <span class="font-bold text-slate-800">{{ $paginator->lastItem() ?? 0 }}</span> 
                dari 
                <span class="font-bold text-slate-800">{{ $paginator->total() }}</span> 
                data
            </div>

            <div class="flex items-center gap-2 flex-1 sm:flex-none justify-between sm:justify-end">
                
                @if ($paginator->onFirstPage())
                    <span class="px-4 py-2 text-sm font-medium text-slate-400 bg-slate-100 rounded-lg border border-slate-200 cursor-not-allowed opacity-60">
                        &larr; Previous
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white rounded-lg border border-slate-300 hover:bg-slate-50 hover:text-slate-900 transition shadow-sm">
                        &larr; Previous
                    </a>
                @endif

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg border border-primary hover:bg-primary/90 transition shadow-sm shadow-indigo-500/20">
                        Next &rarr;
                    </a>
                @else
                    <span class="px-4 py-2 text-sm font-medium text-slate-400 bg-slate-100 rounded-lg border border-slate-200 cursor-not-allowed opacity-60">
                        Next &rarr;
                    </span>
                @endif

            </div>
        </div>
    </div>
@endif  
