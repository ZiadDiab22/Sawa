<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
        $isAdmin = DB::table('user_roles')
            ->where('user_id', $user->id)
            ->where('role_id', 4)
            ->exists();

        if (!$isAdmin) {
            return response()->json([
                'message' => 'Unauthorized - Admin only'
            ], 403);
        }

        return $next($request);
    }
}
