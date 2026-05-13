<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckPermissionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $routeAction = $request->route()?->getAction('controller');
        if (! is_string($routeAction) || ! str_contains($routeAction, '@')) {
            return $next($request);
        }

        [$controllerClass, $method] = explode('@', $routeAction);
        $controller = class_basename($controllerClass);
        $controller = str_replace('Controller', '', $controller);
        $controller = Str::snake($controller);

        $candidates = [
            "{$controller}.{$method}",
            Str::plural($controller).".{$method}",
        ];

        foreach ($candidates as $permission) {
            if ($user->hasPermissionTo($permission, 'sanctum')) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => "Missing permission for {$candidates[0]}",
        ], 403);
    }
}
