<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = null, string $message = 'Success', int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function error(string $message = 'Error', array $errors = [], int $status = 400, ?string $code = null)
    {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => $code,
                'message' => $message,
                'details' => $errors,
            ],
        ], $status);
    }
}