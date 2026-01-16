<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Services\User\Driver\DriverDashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverDashboardController extends Controller
{
    public function __construct(
        private DriverDashboardService $service
    ) {}

    public function index(Request $request)
    {
        $driverId = $request->user()->id;

        return response()->json([
            'status' => true,
            'data'   => $this->service->handle($driverId),
        ]);
    }

    public function show(Request $request)
    {
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
            'data'   => $this->service->stats(
                driverId: Auth::id(),
                period: $request->period,
                from: $request->from,
                to: $request->to
            )
        ]);
    }
}
