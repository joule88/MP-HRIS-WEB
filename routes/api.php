<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PresensiController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\LemburController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\FaceEnrollmentController;
use App\Http\Controllers\Api\PoinController;

use App\Http\Controllers\Api\SignatureApiController;
use App\Http\Controllers\Api\SuratIzinApiController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'index']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/password', [ProfileController::class, 'password']);

    Route::post('/face/enroll', [FaceEnrollmentController::class, 'enrollFace']);
    Route::get('/face/status', [FaceEnrollmentController::class, 'getFaceStatus']);
    Route::post('/face/verify', [FaceEnrollmentController::class, 'verifyFace']);

    Route::get('/poin/expiring', [PoinController::class, 'getExpiringPoints']);
    Route::get('/poin/history', [PoinController::class, 'getPointHistory']);
    Route::post('/poin/redeem', [PoinController::class, 'redeem']);

    Route::get('/schedule/today', [ScheduleController::class, 'today']);
    Route::get('/schedule/check', [ScheduleController::class, 'checkScheduleByDate']);
    Route::get('/schedule/monthly', [ScheduleController::class, 'getMonthlySchedule']);

    Route::get('/presensi', [PresensiController::class, 'index']);
    Route::post('/presensi', [PresensiController::class, 'store']);
    Route::get('/presensi/history', [PresensiController::class, 'history']);
    Route::post('/presensi/{id}/resubmit', [PresensiController::class, 'resubmit']);

    Route::get('/submission/types', [SubmissionController::class, 'types']);
    Route::post('/submission', [SubmissionController::class, 'store']);
    Route::put('/submission/{id}', [SubmissionController::class, 'update']);
    Route::get('/submission/history', [SubmissionController::class, 'history']);

    Route::post('/lembur', [LemburController::class, 'store']);
    Route::put('/lembur/{id}', [LemburController::class, 'update']);
    Route::get('/lembur/history', [LemburController::class, 'history']);

    Route::get('/pengumuman', [\App\Http\Controllers\Api\PengumumanApiController::class, 'index']);

    Route::get('/user', [AuthController::class, 'user']);

    Route::post('/signature', [SignatureApiController::class, 'store']);
    Route::get('/signature', [SignatureApiController::class, 'show']);
    Route::delete('/signature/{id}', [SignatureApiController::class, 'destroy']);

    Route::get('/surat-izin', [SuratIzinApiController::class, 'index']);
    Route::post('/surat-izin', [SuratIzinApiController::class, 'store']);
    Route::get('/surat-izin/{id}', [SuratIzinApiController::class, 'show']);

    // Notifikasi
    Route::prefix('notifikasi')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\NotifikasiApiController::class, 'index']);
        Route::get('/unread-count', [\App\Http\Controllers\Api\NotifikasiApiController::class, 'unreadCount']);
        Route::post('/{id}/read', [\App\Http\Controllers\Api\NotifikasiApiController::class, 'markAsRead']);
        Route::post('/read-all', [\App\Http\Controllers\Api\NotifikasiApiController::class, 'markAllAsRead']);
    });
    Route::post('/device-token', [\App\Http\Controllers\Api\NotifikasiApiController::class, 'saveDeviceToken']);

});
