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

    // ==== Polling Badge Notifikasi ====
    function updateNotifBadge() {
        const badge = document.getElementById('notif-badge');
        if (!badge) return; // User tidak punya akses notifikasi, hentikan tanpa fetch

        fetch('/notifikasi/unread-count', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => {
            if (!r.ok) return null; // Tangani 403/redirect dengan aman
            return r.json();
        })
        .then(data => {
            if (!data) return;
            const count = data.count ?? 0;
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        })
        .catch(() => {});
    }

    updateNotifBadge();
    setInterval(updateNotifBadge, 30000);
})();

// ==== Alpine.js Dropdown Notifikasi ====
function notifDropdown() {
    return {
        open: false,
        items: [],
        unreadCount: 0,
        loading: false,

        toggle() {
            this.open = !this.open;
            if (this.open) this.loadRecent();
        },

        loadRecent() {
            this.loading = true;
            fetch('/notifikasi/recent', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                this.items = data.data || [];
                this.unreadCount = this.items.filter(i => !i.is_read).length;
                this.loading = false;
            })
            .catch(() => { this.loading = false; });
        },

        readItem(item) {
            if (!item.is_read) {
                fetch('/notifikasi/' + item.id + '/read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    item.is_read = true;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                    updateBadgeUI(this.unreadCount);
                });
            }
        },

        markAllRead() {
            fetch('/notifikasi/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).then(() => {
                this.items.forEach(i => i.is_read = true);
                this.unreadCount = 0;
                updateBadgeUI(0);
            });
        },

        getIconClass(tipe) {
            var map = {
                'lembur': 'bg-amber-100 text-amber-600',
                'presensi': 'bg-green-100 text-green-600',
                'pengumuman': 'bg-blue-100 text-blue-600',
                'surat_izin': 'bg-purple-100 text-purple-600',
                'izin': 'bg-purple-100 text-purple-600',
                'poin': 'bg-rose-100 text-rose-600',
            };
            return map[tipe] || 'bg-slate-100 text-slate-600';
        },

        getIcon(tipe) {
            var icons = {
                'lembur': '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
                'presensi': '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
                'pengumuman': '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" /></svg>',
                'surat_izin': '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>',
                'izin': '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>',
                'poin': '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>',
            };
            return icons[tipe] || '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>';
        }
    };
}

function updateBadgeUI(count) {
    var badge = document.getElementById('notif-badge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

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