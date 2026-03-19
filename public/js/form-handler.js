let isFormModified = false;

document.addEventListener('DOMContentLoaded', function() {
    
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');

    if (form) {
        form.addEventListener('change', () => {
            isFormModified = true;
        });

        form.addEventListener('submit', function() {
            isFormModified = false; 

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            }
            if (submitSpinner) submitSpinner.classList.remove('hidden');
            if (submitText) submitText.textContent = 'Menyimpan...';
        });
    }

    window.addEventListener('beforeunload', function(e) {
        if (isFormModified) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    const backLinks = document.querySelectorAll('.btn-back');
    backLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (isFormModified) {
                const confirmLeave = confirm('Anda memiliki perubahan yang belum disimpan. Yakin ingin keluar?');
                if (!confirmLeave) {
                    e.preventDefault();
                }
            }
        });
    });
});