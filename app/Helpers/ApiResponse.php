<?php

namespace App\Helpers;

class ApiResponse
{

    public static function success($data = null, string $message = 'Berhasil', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error(string $message = 'Terjadi kesalahan', int $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    public static function validationError($errors, string $message = 'Validasi gagal')
    {
        return self::error($message, 422, $errors);
    }

    public static function notFound(string $message = 'Data tidak ditemukan')
    {
        return self::error($message, 404);
    }

    public static function unauthorized(string $message = 'Tidak memiliki akses')
    {
        return self::error($message, 401);
    }
}
