/**
 * Image Cropper - Profile Photo
 * Menggunakan Cropper.js untuk crop foto profil berbentuk lingkaran
 */
document.addEventListener('DOMContentLoaded', function () {
    const fotoInput = document.getElementById('foto-input');
    const cropModal = document.getElementById('crop-modal');
    const cropImage = document.getElementById('crop-image');
    const cropSaveBtn = document.getElementById('crop-save-btn');
    const cropCancelBtn = document.getElementById('crop-cancel-btn');
    const cropRotateLeftBtn = document.getElementById('crop-rotate-left');
    const cropRotateRightBtn = document.getElementById('crop-rotate-right');
    const cropResetBtn = document.getElementById('crop-reset');
    const cropZoomInBtn = document.getElementById('crop-zoom-in');
    const cropZoomOutBtn = document.getElementById('crop-zoom-out');

    let cropper = null;

    if (!fotoInput || !cropModal) return;

    fotoInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert('File harus berupa gambar.');
            fotoInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            cropImage.src = event.target.result;
            cropModal.classList.remove('hidden');

            if (cropper) {
                cropper.destroy();
            }

            setTimeout(function () {
                cropper = new Cropper(cropImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.85,
                    cropBoxResizable: false,
                    cropBoxMovable: false,
                    guides: false,
                    center: true,
                    highlight: false,
                    background: false,
                    responsive: true,
                    restore: false,
                    checkCrossOrigin: false,
                    ready: function () {
                        const containerData = cropper.getContainerData();
                        const size = Math.min(containerData.width, containerData.height) * 0.8;
                        cropper.setCropBoxData({
                            left: (containerData.width - size) / 2,
                            top: (containerData.height - size) / 2,
                            width: size,
                            height: size,
                        });
                    },
                });
            }, 100);
        };
        reader.readAsDataURL(file);
    });

    if (cropRotateLeftBtn) {
        cropRotateLeftBtn.addEventListener('click', function () {
            if (cropper) cropper.rotate(-90);
        });
    }

    if (cropRotateRightBtn) {
        cropRotateRightBtn.addEventListener('click', function () {
            if (cropper) cropper.rotate(90);
        });
    }

    if (cropResetBtn) {
        cropResetBtn.addEventListener('click', function () {
            if (cropper) cropper.reset();
        });
    }

    if (cropZoomInBtn) {
        cropZoomInBtn.addEventListener('click', function () {
            if (cropper) cropper.zoom(0.1);
        });
    }

    if (cropZoomOutBtn) {
        cropZoomOutBtn.addEventListener('click', function () {
            if (cropper) cropper.zoom(-0.1);
        });
    }

    cropSaveBtn.addEventListener('click', function () {
        if (!cropper) return;

        cropSaveBtn.disabled = true;
        cropSaveBtn.innerHTML = `
            <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Memproses...
        `;

        const canvas = cropper.getCroppedCanvas({
            width: 512,
            height: 512,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        canvas.toBlob(function (blob) {
            const croppedFile = new File([blob], 'profile-photo.jpg', {
                type: 'image/jpeg',
            });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(croppedFile);
            fotoInput.files = dataTransfer.files;

            const preview = document.getElementById('preview-foto');
            const previewNew = document.getElementById('preview-foto-new');
            const placeholder = document.getElementById('placeholder-foto');

            const url = URL.createObjectURL(blob);
            if (preview) {
                preview.src = url;
            } else {
                if (placeholder) placeholder.classList.add('hidden');
                if (previewNew) {
                    previewNew.src = url;
                    previewNew.classList.remove('hidden');
                }
            }

            closeCropModal();
        }, 'image/jpeg', 0.9);
    });

    cropCancelBtn.addEventListener('click', function () {
        fotoInput.value = '';
        closeCropModal();
    });

    cropModal.addEventListener('click', function (e) {
        if (e.target === cropModal) {
            fotoInput.value = '';
            closeCropModal();
        }
    });

    function closeCropModal() {
        cropModal.classList.add('hidden');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        cropSaveBtn.disabled = false;
        cropSaveBtn.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Simpan
        `;
    }
});
