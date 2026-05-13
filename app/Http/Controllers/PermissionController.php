<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:roles.view,sanctum')];
    }

    public function index(): JsonResponse
    {
        $permissions = Permission::orderBy('id')->get(['id', 'name']);

        return response()->json($permissions);
    }
}
