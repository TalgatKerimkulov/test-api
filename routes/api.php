<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BatchProfitController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientRefundController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductAvailabilityController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseRefundController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\StorageReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::bind('client', fn ($value) => User::query()->where('id', $value)->firstOrFail());

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::apiResource('users', UserController::class);

        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::post('/users/{user}/roles', [UserRoleController::class, 'store']);

        Route::apiResource('providers', ProviderController::class);
        Route::apiResource('clients', ClientController::class)->parameters(['clients' => 'client']);
        Route::apiResource('categories', CategoryController::class);

        Route::get('/products/available', [ProductAvailabilityController::class, 'index'])
            ->middleware('permission:products.view,sanctum');
        Route::apiResource('products', ProductController::class);

        Route::get('/storages/remaining-quantities', [StorageReportController::class, 'remaining'])
            ->middleware('permission:reports.stock_remaining,sanctum');
        Route::apiResource('storages', StorageController::class);

        Route::post('/purchases', [PurchaseController::class, 'store'])
            ->middleware('permission:purchases.create,sanctum');

        Route::post('/provider-refunds', [PurchaseRefundController::class, 'store'])
            ->middleware('permission:purchases.refund,sanctum');

        Route::post('/client-orders', [OrderController::class, 'store'])
            ->middleware('permission:client_orders.create,sanctum');

        Route::post('/client-refunds', [ClientRefundController::class, 'store'])
            ->middleware('permission:client_orders.refund,sanctum');

        Route::get('/batches/profit', [BatchProfitController::class, 'index'])
            ->middleware('permission:reports.batch_profit,sanctum');
    });
});
