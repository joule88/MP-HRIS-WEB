@props(['message' => 'Data tidak ditemukan', 'hint' => 'Coba ubah kata kunci pencarian atau filter.'])

<tr>
    <td {{ $attributes }} class="px-6 py-12 text-center text-slate-400">
        <div class="flex flex-col items-center justify-center">
            
            <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg font-medium text-slate-500">{{ $message }}</p>
            @if($hint)
                <p class="text-sm text-slate-400">{{ $hint }}</p>
            @endif
        </div>
    </td>
</tr>
