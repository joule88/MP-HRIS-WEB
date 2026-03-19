@props(['name', 'label' => 'Foto Profil', 'default' => null, 'placeholder' => null])

<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
    <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">{{ $label }}</h3>
    
    <div x-data="{ photoPreview: null }" class="space-y-4">
        <div class="flex flex-col items-center">
            
            <template x-if="!photoPreview">
                @if($default)
                    <img src="{{ $default }}" class="w-32 h-32 rounded-2xl object-cover border-2 border-primary ring-4 ring-primary/10 shadow-lg">
                @else
                    <div class="w-32 h-32 rounded-2xl bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center text-slate-400">
                        
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                @endif
            </template>

            <template x-if="photoPreview">
                <img :src="photoPreview" class="w-32 h-32 rounded-2xl object-cover border-2 border-primary ring-4 ring-primary/10 shadow-lg">
            </template>
        </div>

        <div class="relative">
            <input type="file" name="{{ $name }}" accept="image/*" class="hidden" id="{{ $name }}-input" 
                   @change="const file = $event.target.files[0]; 
                            if (file) { 
                                if (file.size > 5242880) { 
                                    Swal.fire('Error', 'Ukuran file maksimal 5MB', 'error'); 
                                    $event.target.value = ''; 
                                    return; 
                                } 
                                const reader = new FileReader(); 
                                reader.onload = (e) => { photoPreview = e.target.result; }; 
                                reader.readAsDataURL(file); 
                            }">
            <label for="{{ $name }}-input" class="block w-full text-center px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer transition-all">
                Pilih Foto
            </label>
        </div>

        @error($name)
            <p class="text-red-500 text-xs mt-1 text-center">{{ $message }}</p>
        @enderror
        <p class="text-[10px] text-slate-400 text-center uppercase tracking-wider font-bold">Format: JPG, PNG (Maks. 5MB)</p>
    </div>
</div>
