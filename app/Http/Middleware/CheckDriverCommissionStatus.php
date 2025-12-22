<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckDriverCommissionStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $driverProfile = $user->driverProfile;

        if (
            $driverProfile &&
            $driverProfile->status === 'suspended' &&
            $driverProfile->is_status === 'inactive'
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Your account is suspended. Please pay the due commission to reactivate your account.'
            ], 403);
        }

        return $next($request);
    }
}
