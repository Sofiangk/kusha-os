<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Rate limiting for API
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\ThrottleRequests::class . ':60,1',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle validation errors
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Validation failed',
                        'details' => $e->errors(),
                    ]
                ], 422);
            }
        });

        // Handle InvalidArgumentException
        $exceptions->render(function (\InvalidArgumentException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_ARGUMENT',
                        'message' => $e->getMessage(),
                        'details' => []
                    ]
                ], 400);
            }
        });

        // Handle model not found
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Resource not found',
                        'details' => []
                    ]
                ], 404);
            }
        });
    })->create();