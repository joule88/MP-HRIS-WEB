document.addEventListener('DOMContentLoaded', function() {
});

function confirmDelete(id) {
    Swal.fire({
        html: `
            <div class="flex flex-col items-center py-2">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4 ring-8 ring-red-50">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Hapus Data?</h2>
                <p class="text-sm text-slate-500 text-center px-4 mt-2 leading-relaxed">
                    Data yang dihapus akan hilang secara permanen dari sistem dan tidak dapat dikembalikan.
                </p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus Permanen',
        cancelButtonText: 'Batalkan',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'px-6 py-2.5 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition shadow-lg shadow-red-200 mr-2',
            cancelButton: 'px-6 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition'
        },
        width: '400px',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                html: `
                    <div class="flex flex-col items-center py-6">
                        <div class="animate-spin rounded-full h-10 w-10 border-4 border-slate-200 border-t-red-600 mb-4"></div>
                        <p class="text-sm font-bold text-slate-700">Menghapus Data...</p>
                    </div>
                `,
                width: '250px',
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            const form = document.getElementById('delete-form-' + id);
            if (form) form.submit();
        }
    })
}