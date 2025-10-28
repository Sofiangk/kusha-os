<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function respond($data = null, string $message = 'Success', int $status = 200)
    {
        return ApiResponse::success($data, $message, $status);
    }

    protected function respondError(string $message, array $errors = [], int $status = 400, ?string $code = null)
    {
        return ApiResponse::error($message, $errors, $status, $code);
    }
}