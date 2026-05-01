<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\KantorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\LemburController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanLemburController;
use App\Http\Controllers\FaceApprovalController;
use App\Http\Controllers\SuratIzinController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\CutiController;

Route::get('/', [AuthController::class, 'index'])->name('home')->middleware('guest');
Route::get('/login', [AuthController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    Route::get('/tanda-tangan', [SignatureController::class, 'show'])->name('signature.show');
    Route::post('/tanda-tangan', [SignatureController::class, 'store'])->name('signature.store');
});

Route::middleware(['auth', 'role:hrd,manager'])->group(function () {
    Route::get('/notifikasi', [\App\Http\Controllers\NotifikasiWebController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/unread-count', [\App\Http\Controllers\NotifikasiWebController::class, 'unreadCount'])->name('notifikasi.unread-count');
    Route::get('/notifikasi/recent', [\App\Http\Controllers\NotifikasiWebController::class, 'recent'])->name('notifikasi.recent');
    Route::post('/notifikasi/read-all', [\App\Http\Controllers\NotifikasiWebController::class, 'markAllAsRead'])->name('notifikasi.read-all');
    Route::post('/notifikasi/{id}/read', [\App\Http\Controllers\NotifikasiWebController::class, 'markAsRead'])->name('notifikasi.read');

    Route::get('/izin', [App\Http\Controllers\PengajuanIzinController::class, 'index'])->name('izin.index');
    Route::post('/izin', [App\Http\Controllers\PengajuanIzinController::class, 'store'])->name('izin.store');
    Route::post('/izin/{id}/approve', [App\Http\Controllers\PengajuanIzinController::class, 'approve'])->name('izin.approve');
    Route::post('/izin/{id}/reject', [App\Http\Controllers\PengajuanIzinController::class, 'reject'])->name('izin.reject');

    Route::get('/surat-izin', [SuratIzinController::class, 'index'])->name('surat-izin.index');
    Route::get('/surat-izin/{id}', [SuratIzinController::class, 'show'])->name('surat-izin.show');
    Route::post('/surat-izin/{id}/approve', [SuratIzinController::class, 'approve'])->name('surat-izin.approve');
    Route::post('/surat-izin/{id}/reject', [SuratIzinController::class, 'reject'])->name('surat-izin.reject');

    Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
    Route::get('/pegawai/create', [PegawaiController::class, 'create'])->name('pegawai.create');
    Route::get('/pegawai/{id}', [PegawaiController::class, 'show'])->name('pegawai.show');
});

Route::middleware(['auth', 'role:hrd,manager,supervisor'])->group(function () {
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');

    Route::prefix('jadwal')->name('jadwal.')->group(function () {
        Route::get('/generate', [App\Http\Controllers\JadwalController::class, 'generateForm'])->name('generate');
        Route::post('/generate', [App\Http\Controllers\JadwalController::class, 'processGenerate'])->name('process');
        Route::get('/events', [App\Http\Controllers\JadwalController::class, 'getEvents'])->name('events');
        Route::post('/check-conflicts', [App\Http\Controllers\JadwalController::class, 'checkConflicts'])->name('check-conflicts');
        Route::post('/bulk-delete', [App\Http\Controllers\JadwalController::class, 'bulkDestroy'])->name('bulk-delete');
    });
    Route::resource('jadwal', \App\Http\Controllers\JadwalController::class)->except(['edit', 'show']);

    Route::get('/tukar-shift/jadwal-user', [\App\Http\Controllers\TukarShiftController::class, 'getJadwalUser'])->name('tukar-shift.jadwal-user');
    Route::resource('tukar-shift', \App\Http\Controllers\TukarShiftController::class)->only(['index', 'create', 'store']);

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [LaporanController::class, 'exportExcel'])->name('laporan.export');
    Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.exportPdf');

    Route::get('/laporan/izin', [LaporanController::class, 'cuti'])->name('laporan.izin');
    Route::get('/laporan/izin/export', [LaporanController::class, 'exportIzinExcel'])->name('laporan.izin.export');
});

Route::middleware(['auth', 'role:hrd'])->group(function () {
    Route::post('/presensi/bulk-action', [PresensiController::class, 'bulkAction'])->name('presensi.bulkAction');
    Route::post('/presensi/{id}/approve', [PresensiController::class, 'approve'])->name('presensi.approve');
    Route::post('/presensi/{id}/reject', [PresensiController::class, 'reject'])->name('presensi.reject');

    Route::resource('pengumuman', PengumumanController::class);

    Route::resource('lembur', LemburController::class)->only(['index', 'update', 'create', 'store']);

    Route::get('/penggunaan-poin', [App\Http\Controllers\PenggunaanPoinController::class, 'index'])->name('penggunaan-poin.index');
    Route::put('/penggunaan-poin/{id}', [App\Http\Controllers\PenggunaanPoinController::class, 'update'])->name('penggunaan-poin.update');

    Route::resource('divisi', DivisiController::class)->except(['create', 'edit', 'show']);
    Route::resource('jabatan', JabatanController::class);
    Route::resource('kantor', KantorController::class)->except(['create', 'show', 'edit']);
    Route::resource('shift', \App\Http\Controllers\ShiftController::class)->except(['create', 'edit', 'show']);
    Route::resource('pegawai', PegawaiController::class)->except(['index', 'show']);
    Route::post('/pegawai/{id}/reset-password', [PegawaiController::class, 'resetPassword'])->name('pegawai.reset-password');
    Route::post('/hari-libur/sync', [\App\Http\Controllers\HariLiburController::class, 'syncHolidays'])->name('hari-libur.sync');
    Route::resource('hari-libur', \App\Http\Controllers\HariLiburController::class)->except(['create', 'edit', 'show']);

    Route::get('/presensi/create', [PresensiController::class, 'create'])->name('presensi.create');
    Route::post('/presensi/manual', [PresensiController::class, 'storeManual'])->name('presensi.storeManual');
    Route::get('/presensi/{id}/edit', [PresensiController::class, 'edit'])->name('presensi.edit');
    Route::put('/presensi/{id}/manual', [PresensiController::class, 'updateManual'])->name('presensi.updateManual');

    Route::get('/cuti', [CutiController::class, 'index'])->name('cuti.index');
    Route::put('/cuti/{id}', [CutiController::class, 'update'])->name('cuti.update');
    Route::post('/cuti/reset', [CutiController::class, 'resetMassal'])->name('cuti.reset');

    Route::get('/laporan-lembur', [LaporanLemburController::class, 'index'])->name('laporan-lembur.index');
    Route::get('/laporan-lembur/export-excel', [LaporanLemburController::class, 'exportExcel'])->name('laporan-lembur.exportExcel');

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permission.index');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permission.store');
    Route::post('/permissions/sync/{id_role}', [PermissionController::class, 'sync'])->name('permission.sync');

    Route::get('/face-approval', [FaceApprovalController::class, 'index'])->name('face.index');
    Route::post('/face-approval/reextract-all', [FaceApprovalController::class, 'reextractAll'])->name('face.reextract_all');
    Route::get('/face-approval/photo/{userId}/{pose}', [FaceApprovalController::class, 'showPhoto'])->name('face.photo');
    Route::get('/face-approval/frame/{userId}/{frameIndex}', [FaceApprovalController::class, 'showFrame'])->name('face.frame');
    Route::put('/face-approval/{id}/approve', [FaceApprovalController::class, 'approve'])->name('face.approve');
    Route::delete('/face-approval/{id}/reject', [FaceApprovalController::class, 'reject'])->name('face.reject');
    Route::delete('/face-approval/{id}/reset', [FaceApprovalController::class, 'reset'])->name('face.reset');

    Route::resource('role', RoleController::class)->except(['create', 'edit']);
});
