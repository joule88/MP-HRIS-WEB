@props(['name', 'title'])

<div x-data="{ show: false, name: '{{ $name }}' }"
     x-show="show"
     x-on:open-modal.window="if ($event.detail == name) show = true"
     x-on:close-modal.window="if ($event.detail == name) show = false"
     x-on:keydown.escape.window="show = false"
     style="display: none;"
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">

    <div x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 backdrop-blur-none"
         x-transition:enter-end="opacity-100 backdrop-blur-sm"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 backdrop-blur-sm"
         x-transition:leave-end="opacity-0 backdrop-blur-none"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-all" 
         @click="show = false"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-[0_20px_60px_-15px_rgba(0,0,0,0.3)] transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100/50">
            
            <div class="bg-white/80 backdrop-blur-md px-6 pb-4 pt-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center z-10 sticky top-0">
                <h3 class="text-lg font-bold leading-6 text-slate-800" id="modal-title">
                    {{ $title }}
                </h3>
                <button @click="show = false" type="button" class="text-slate-400 hover:text-slate-500 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-4 py-5 sm:p-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
