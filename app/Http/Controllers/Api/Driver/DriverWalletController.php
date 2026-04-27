<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverProfile;
use App\Models\WalletTransaction;
use App\Services\NotificationService;
use App\Services\User\Driver\DriverWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverWalletController extends Controller
{
    public function __construct(
        private DriverWalletService $service
    )
    {
    }

    public function add(Request $request, int $id)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        if (!DriverProfile::where('user_id', $id)->exists())
            throw new \Exception('Invalid driver id');

        $profile = $this->service->add(
            $id,
            (float)$request->amount
        );


    $driver = \App\Models\User::find($id);

    $firebaseResult = NotificationService::send(
        $driver->id,
        'wallet_topup',
        'تم إضافة رصيد 💰',
        'تم إضافة ' . $request->amount . ' إلى محفظتك',
        [
            'amount' => (string) $request->amount
        ]
    );

        return response()->json(['status' => true, 'message' => $profile  ,
            'notification' => [
            'type' => 'wallet_topup',
            'title' => 'تم إضافة رصيد 💰',
            'body' => 'تم إضافة ' . $request->amount . ' إلى محفظتك',
            'firebase_result' => $firebaseResult
        ]]);
    }

    public function showWallet()
    {
        $userId = Auth::id();

        $wallet = $this->service->getWallet($userId);

        return response()->json([
            'message' => 'Driver wallet retrieved successfully',
            'data' => [
                'wallet' => $wallet
            ]
        ]);
    }

    public function get()
    {
        $userId = Auth::id();
        $data = $this->service->getWalletData($userId);
        return response()->json(['status' => true, 'data' => $data]);
    }
}
