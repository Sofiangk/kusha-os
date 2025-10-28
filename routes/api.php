<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\InvoiceController;

Route::prefix('v1')->group(function () {
    Route::get('/ping', fn() => response()->json(['status' => 'ok', 'version' => 'v1', 'time' => now()]));

    // Public auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', fn(\Illuminate\Http\Request $r) => $r->user());

        // Categories
        Route::apiResource('categories', CategoryController::class);

        // Products
        Route::apiResource('products', ProductController::class);

        // Clients
        Route::apiResource('clients', ClientController::class);
        Route::post('clients/search-phone', [ClientController::class, 'searchByPhone']);

        // Orders
        Route::apiResource('orders', OrderController::class);
        Route::post('orders/with-new-client', [OrderController::class, 'storeWithNewClient']);

        // Reports & Analytics
        Route::prefix('reports')->group(function () {
            Route::get('/revenue', [ReportController::class, 'revenue']);
            Route::get('/best-selling', [ReportController::class, 'bestSellingProducts']);
            Route::get('/recurring-clients', [ReportController::class, 'recurringClients']);
            Route::get('/orders-by-status', [ReportController::class, 'ordersByStatus']);
            Route::get('/orders-by-date', [ReportController::class, 'ordersByDate']);
            Route::get('/category-performance', [ReportController::class, 'categoryPerformance']);
            Route::get('/client/{client}', [ReportController::class, 'clientOrderHistory']);
        });

        // Invoices
        Route::get('invoices', [InvoiceController::class, 'index']);
        Route::get('invoices/{order}', [InvoiceController::class, 'show']);
        Route::get('orders/{order}/invoice', [InvoiceController::class, 'generate']);

        // Discounts
        Route::apiResource('discounts', DiscountController::class);
    });
});
