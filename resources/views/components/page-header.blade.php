<div {{ $attributes->merge(['class' => 'flex flex-col md:flex-row md:items-center justify-between gap-4']) }}>
    <div>
        <h2 class="text-2xl font-bold text-slate-800">{{ $title }}</h2>
        @if(isset($subtitle))
            <p class="text-slate-500 text-sm">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="flex items-center gap-3 w-full md:w-auto">
        {{ $slot }}
    </div>
</div>
