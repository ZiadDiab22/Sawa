<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckDriver
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
        $isDriver = DB::table('user_roles')
            ->where('user_id', $user->id)
            ->where('role_id', 2)
            ->exists();

        if (!$isDriver) {
            return response()->json([
                'message' => 'Unauthorized - Driver only'
            ], 403);
        }

        return $next($request);
    }
}
