<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\JenisKompensasi;

class KompensasiController extends Controller
{

    public function index()
    {
        try {
            $kompensasi = JenisKompensasi::all();
            return ApiResponse::success($kompensasi);
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal memuat jenis kompensasi: ' . $e->getMessage(), 500);
        }
    }
}
