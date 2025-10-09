<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Dùng: ->middleware('role:0') cho admin
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (! $request->user() || $request->user()->role != $role) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        return $next($request);
    }
}
