<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\Admin\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $result = $this->adminService->login($data);

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        $validated = $request->validated();

        $updated = $this->adminService->updateProfile($user->id, $validated);

        if (!$updated) {
            return response()->json(['message' => 'something went error'], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'update successful',
            'user' => $updated,
        ]);
    }
}
