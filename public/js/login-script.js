document.addEventListener('DOMContentLoaded', function() {
    
    const toggleBtn = document.getElementById('toggle-password-btn');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    const eyeOffIcon = document.getElementById('eye-off-icon');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                if (eyeIcon) eyeIcon.style.display = 'none';
                if (eyeOffIcon) eyeOffIcon.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                if (eyeIcon) eyeIcon.style.display = 'block';
                if (eyeOffIcon) eyeOffIcon.style.display = 'none';
            }
        });
    }

    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            `;
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');

            const formData = new FormData(loginForm);

            fetch(loginForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                if (response.status === 419) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sesi Kedaluwarsa',
                        text: 'Halaman akan dimuat ulang, silakan coba lagi.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#1e293b',
                        width: '400px',
                    }).then(() => {
                        window.location.reload();
                    });
                    return;
                }

                const contentType = response.headers.get('content-type') || '';
                const isJson = contentType.includes('application/json');
                let data = null;

                try {
                    data = isJson ? await response.json() : null;
                } catch (parseError) {
                    data = null;
                }

                if (response.ok && data?.success) {
                    window.location.href = data.redirect || '/dashboard';
                } else {
                    let errorMessage = 'Email atau Password yang Anda masukkan salah.';
                    
                    if (data?.message) {
                        errorMessage = data.message;
                    }

                    if (data?.errors) {
                        const errorList = Object.values(data.errors).flat();
                        errorMessage = `<ul class="text-left list-disc list-inside">${errorList.map(err => `<li>${err}</li>`).join('')}</ul>`;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Login Gagal',
                        html: errorMessage,
                        confirmButtonText: 'Coba Lagi',
                        confirmButtonColor: '#1e293b',
                        width: '400px',
                    });
                    
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi Bermasalah',
                    text: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.',
                    confirmButtonText: 'Muat Ulang',
                    confirmButtonColor: '#1e293b',
                    width: '400px'
                }).then(() => {
                    window.location.reload();
                });
            });
        });
    }
});