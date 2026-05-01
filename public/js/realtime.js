document.addEventListener('DOMContentLoaded', function () {
    if (!window.Echo) return;
    if (!window.__authUser) return;

    var userId = window.__authUser.id;
    var userRole = window.__authUser.role;

    window.Echo.private('user.' + userId)
        .listen('.NotifikasiCreated', function (e) {
            updateNotifBadge(e.unread_count);
            showToast(e.judul, e.pesan);
            refreshCurrentPage();
        })
        .listen('.PengajuanIzinUpdated', function () {
            refreshCurrentPage();
        })
        .listen('.SuratIzinUpdated', function () {
            refreshCurrentPage();
        })
        .listen('.PresensiUpdated', function () {
            refreshCurrentPage();
        })
        .listen('.LemburUpdated', function () {
            refreshCurrentPage();
        })
        .listen('.PenggunaanPoinUpdated', function () {
            refreshCurrentPage();
        })
        .listen('.CutiUpdated', function () {
            refreshCurrentPage();
        })
        .listen('.TukarShiftUpdated', function () {
            refreshCurrentPage();
        })
        .listen('.JadwalUpdated', function () {
            refreshCurrentPage();
        })
        .listen('.FaceEnrollmentUpdated', function () {
            refreshCurrentPage();
        });

    if (userRole === 'hrd' || userRole === 'super_admin') {
        window.Echo.private('hrd')
            .listen('.PengajuanBaru', function (e) {
                showToast('Pengajuan Baru', e.namaPegawai + ' - ' + e.detail);
                refreshCurrentPage();
            })
            .listen('.PresensiMasuk', function (e) {
                showToast('Presensi Masuk', e.namaPegawai + ' absen masuk jam ' + e.jamMasuk);
            });
    }

    window.Echo.channel('pengumuman')
        .listen('.PengumumanCreated', function (e) {
            showToast('📢 Pengumuman', e.judul);
            refreshCurrentPage();
        });

    function updateNotifBadge(count) {
        var badge = document.getElementById('notif-badge');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function showToast(title, message) {
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: title,
            text: message,
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
        });
    }

    function refreshCurrentPage() {
        if (window.Turbo) {
            window.Turbo.visit(window.location.href, { action: 'replace' });
        }
    }
});
