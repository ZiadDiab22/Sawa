<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\CompanyProfitService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CompanyProfitController extends Controller
{
    public function show(Request $request,CompanyProfitService $service)
    {
        $request->validate([
            'period' => ['required', function ($attr, $value, $fail) {
                $allowed = [
                    'today',
                    'last_7_days',
                    'last_30_days',
                    'this_month',
                    'custom',
                    'all'
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
                period: $request->period,
                from: $request->from,
                to: $request->to,
            )
        ]);
    }

}
