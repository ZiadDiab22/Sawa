<?php

namespace App\Http\Controllers\Api\Passenger;

use App\Http\Controllers\Controller;
use App\Services\User\Passenger\ProfileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    public function show()
    {
        return response()->json(
            $this->profileService->getProfile(Auth::id())
        );
    }

    public function update(Request $request)
    {
        $request->validate([
            'name'  => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,banned',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|unique:users,phone,' . Auth::id(),
            'profile_image' => 'nullable|image|max:2048',
        ]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $this->profileService->updateProfile(
                Auth::id(),
                $request->all()
            )
        ]);
    }
}
