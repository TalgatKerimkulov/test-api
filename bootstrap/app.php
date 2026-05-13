<?php

use App\Exceptions\DomainException;
use App\Exceptions\ApiException;
use App\Http\Middleware\AdminScopeMiddleware;
use App\Http\Middleware\CheckPermissionMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'admin.scope' => AdminScopeMiddleware::class,
            'check.permission' => CheckPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ApiException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'message' => $e->getMessage(),
                        'code' => $e->errorCode() ?? $e->httpStatus(),
                    ],
                    'result' => null,
                ], $e->httpStatus());
            }
        });

        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json($e->toArray(), $e->statusCode());
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error'   => 'validation_error',
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error'   => 'unauthenticated',
                    'message' => $e->getMessage(),
                ], 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error'   => 'forbidden',
                    'message' => $e->getMessage(),
                ], 403);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error'   => 'forbidden',
                    'message' => $e->getMessage() ?: 'This action is unauthorized.',
                ], 403);
            }
        });
    })->create();
