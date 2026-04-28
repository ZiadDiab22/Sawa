<?php

namespace App\Services\User\Driver;

use App\Models\CompanyCommission;
use App\Models\DriverProfile;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Repositories\Driver\DriverWalletRepository;
use App\Repositories\DriverRepository;
use App\Repositories\Ride\ProfitRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverWalletService
{
    public function __construct(
        private DriverRepository       $drivers,
        private ProfitRepository       $profits,
        private DriverWalletRepository $repo,
    )
    {
    }

    public function add(int $driverId, float $amount)
    {
        return DB::transaction(function () use ($driverId, $amount) {

            $this->drivers->incrementWallet($driverId, $amount);

            $LE = (int)Setting::where('key', 'LE')->value('value');

            $wallet = (int)DriverProfile::where('user_id', $driverId)->value('wallet');

            if ($wallet > -(20 * $LE)) {
                $this->drivers->updateStatus($driverId, 'approved');
            }

            WalletTransaction::query()->create([
                'user_id' => $driverId,
                'type' => 'credit',
                'reason' => 'wallet_charge',
                'amount' => $amount,
            ]);

            return DriverProfile::where('user_id', $driverId)->get(['id', 'user_id', 'wallet', 'status']);
        });
    }

    public function getWallet(int $userId): float
    {
        return $this->drivers->getWalletByUserId($userId);
    }

    public function getWalletData(int $userId): array
    {
        $wallet = $this->repo->getDriverWallet($userId);
        $transactions = $this->repo->getTransactions($userId);
        $profit = $this->profits->sumForDriverByDate($userId, Carbon::today());

        $transactions = $transactions->map(function ($t) {
            return ['id' => $t->id,
                'type' => $t->type,
                'reason' => $t->reason,
                'amount' => $t->amount,
                'ride_id' => $t->ride_id,
                'date' => $t->created_at->format('Y-m-d'),
                'time' => $t->created_at->format('H:i:s'),];
        });

        return ['profit' => $profit,'wallet' => $wallet, 'transactions' => $transactions];
    }
}
