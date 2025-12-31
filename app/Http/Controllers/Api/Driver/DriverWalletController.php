<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverProfile;
use App\Services\User\Driver\DriverWalletService;
use Illuminate\Http\Request;

class DriverWalletController extends Controller
{
    public function __construct(
        private DriverWalletService $service
    ) {}

    public function add(Request $request, int $id)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        if (!DriverProfile::where('user_id', $id)->exists())
            throw new \Exception('Invalid driver id');

        $profile = $this->service->add(
            $id,
            (float) $request->amount
        );

        return response()->json(['status' => true, 'message' => $profile]);
    }
}
