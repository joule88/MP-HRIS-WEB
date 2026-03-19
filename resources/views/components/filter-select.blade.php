@props(['id' => null, 'name' => null])

<div class="relative group">
    <select id="{{ $id ?? $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'w-full pl-3 pr-10 py-2 bg-white border border-slate-300 rounded-lg text-sm appearance-none outline-none focus:ring-2 focus:ring-[#130F26] transition cursor-pointer truncate']) }}>
        {{ $slot }}
    </select>

    <div
        class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#130F26] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>
</div>
