<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\User\Driver\DriverDashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverProfitController extends Controller
{
    public function show(Request $request, int $id, DriverDashboardService $service)
    {
        try {
            $service->check($id);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => 'wrong_id'
            ], 404);
        }

        $request->validate([
            'period' => ['required', function ($attr, $value, $fail) {
                $allowed = [
                    'today',
                    'last_7_days',
                    'last_30_days',
                    'this_month',
                    'custom',
                ];

                if (!in_array($value, $allowed) && !Carbon::hasFormat($value, 'Y-m-d')) {
                    $fail('The period must be a valid keyword or date');
                }
            }],

            'from' => [
                'required_if:period,custom',
                'date',
            ],

            'to' => [
                'required_if:period,custom',
                'date',
                'after_or_equal:from',
            ],
        ]);

        return response()->json([
            'status' => true,
            'data' => $service->stats(
                driverId: $id,
                period: $request->period,
                from: $request->from,
                to: $request->to,
                forAdmin: true,
            )
        ]);
    }
}
