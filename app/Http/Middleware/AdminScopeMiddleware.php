<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminScopeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if ($user->type !== UserType::Admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required',
            ], 403);
        }

        return $next($request);
    }
}
