(function() {
    const swalData = document.getElementById('swal-data');
    const flashError = document.querySelector('.flash-data-error');
    const flashSuccess = document.querySelector('.flash-data-success');

    function showSuccess(msg) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: msg,
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            timerProgressBar: true,
            background: '#fff',
            color: '#130F26'
        });
    }

    function showError(msg) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: msg,
            confirmButtonColor: '#1e293b',
        });
    }

    if (swalData) {
        const successMessage = swalData.getAttribute('data-success');
        const errorMessage = swalData.getAttribute('data-error');

        if (successMessage && successMessage !== '') {
            showSuccess(successMessage);
            swalData.setAttribute('data-success', '');
        }

        if (errorMessage && errorMessage !== '') {
            showError(errorMessage);
            swalData.setAttribute('data-error', '');
        }

        const validationErrors = swalData.getAttribute('data-errors');
        if (validationErrors) {
            try {
                const errors = JSON.parse(validationErrors);
                if (errors.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Validasi!',
                        html: `<ul class="text-left list-disc list-inside">${errors.map(err => `<li>${err}</li>`).join('')}</ul>`,
                        confirmButtonColor: '#d33',
                    });
                }
            } catch (e) {}
            swalData.setAttribute('data-errors', '[]');
        }
    }

    if (flashError) {
        const msg = flashError.getAttribute('data-message');
        if (msg && msg !== '') {
            showError(msg);
            flashError.setAttribute('data-message', '');
        }
    }
    if (flashSuccess) {
        const msg = flashSuccess.getAttribute('data-message');
        if (msg && msg !== '') {
            showSuccess(msg);
            flashSuccess.setAttribute('data-message', '');
        }
    }
})();

function confirmDelete(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data ini akan dihapus secara permanen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus saja!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('delete-form-' + id);
            if (form) {
                form.submit();
            } else {
                console.error('Form hapus tidak ditemukan untuk ID:', id);
            }
        }
    });
    }

function confirmAction(event, formId, message, confirmBtnColor = '#3085d6', confirmBtnText = 'Ya, lanjutkan!') {
    event.preventDefault();

    Swal.fire({
        title: 'Konfirmasi Tindakan',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: confirmBtnColor,
        cancelButtonColor: '#64748b',
        confirmButtonText: confirmBtnText,
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById(formId);
            if (form) {
                form.submit();
            } else {
                console.error('Form tidak ditemukan:', formId);
            }
        }
    });
}