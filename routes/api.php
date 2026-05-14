<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\FileController as AdminFileController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProviderController as AdminProviderController;
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
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/admin/login', [AuthController::class, 'adminLogin']);
    Route::post('/auth/client/login', [AuthController::class, 'clientLogin']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/client/company-create', [AuthController::class, 'createClientCompany']);

        Route::apiResource('users', UserController::class);

        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::post('/users/{user}/roles', [UserRoleController::class, 'store']);

        Route::apiResource('providers', ProviderController::class);
        Route::apiResource('clients', ClientController::class);
        Route::apiResource('categories', CategoryController::class);

        Route::prefix('admin')->middleware(['admin.scope', 'check.permission'])->group(function (): void {
            Route::prefix('category')->group(function (): void {
                Route::post('/create', [AdminCategoryController::class, 'create']);
                Route::get('/index', [AdminCategoryController::class, 'index']);
                Route::get('/show', [AdminCategoryController::class, 'show']);
                Route::post('/update', [AdminCategoryController::class, 'update']);
                Route::post('/delete', [AdminCategoryController::class, 'delete']);
                Route::get('/item-list', [AdminCategoryController::class, 'itemList']);
            });

            Route::prefix('provider')->group(function (): void {
                Route::post('/create', [AdminProviderController::class, 'create']);
                Route::get('/index', [AdminProviderController::class, 'index']);
                Route::get('/show', [AdminProviderController::class, 'show']);
                Route::post('/update', [AdminProviderController::class, 'update']);
                Route::post('/delete', [AdminProviderController::class, 'delete']);
                Route::get('/item-list', [AdminProviderController::class, 'itemList']);
            });

            Route::prefix('product')->group(function (): void {
                Route::post('/create', [AdminProductController::class, 'create']);
                Route::get('/index', [AdminProductController::class, 'index']);
                Route::get('/show', [AdminProductController::class, 'show']);
                Route::post('/update', [AdminProductController::class, 'update']);
                Route::post('/delete', [AdminProductController::class, 'delete']);
                Route::get('/item-list', [AdminProductController::class, 'itemList']);
            });

            Route::prefix('file')->group(function (): void {
                Route::post('/create', [AdminFileController::class, 'create']);
                Route::get('/index', [AdminFileController::class, 'index']);
                Route::get('/show', [AdminFileController::class, 'show']);
                Route::post('/update', [AdminFileController::class, 'update']);
                Route::post('/delete', [AdminFileController::class, 'delete']);
                Route::get('/item-list', [AdminFileController::class, 'itemList']);
            });
        });

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

        Route::prefix('client')->group(function (): void {
            Route::post('/orders', [OrderController::class, 'store'])
                ->middleware('permission:client_orders.create,sanctum');

            Route::post('/refunds', [ClientRefundController::class, 'store'])
                ->middleware('permission:client_orders.refund,sanctum');
        });

        // Backward-compatible aliases for legacy clients.
        Route::post('/client-orders', [OrderController::class, 'store'])
            ->middleware('permission:client_orders.create,sanctum');
        Route::post('/client-refunds', [ClientRefundController::class, 'store'])
            ->middleware('permission:client_orders.refund,sanctum');

        Route::get('/batches/profit', [BatchProfitController::class, 'index'])
            ->middleware('permission:reports.batch_profit,sanctum');
    });
});
