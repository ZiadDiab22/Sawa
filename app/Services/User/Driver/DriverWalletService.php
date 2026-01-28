<?php

namespace App\Services\User\Driver;

use App\Models\CompanyCommission;
use App\Models\DriverProfile;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Repositories\DriverRepository;
use App\Repositories\Ride\ProfitRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverWalletService
{
  public function __construct(
    private DriverRepository $drivers,
    private ProfitRepository $profits,
  ) {}

  public function add(int $driverId, float $amount)
  {
    return DB::transaction(function () use ($driverId, $amount) {

      $this->drivers->incrementWallet($driverId, $amount);

      WalletTransaction::create([
        "user_id" => $driverId,
        "employee_id" => Auth::id(),
        "amount" => $amount
      ]);

      $LE = (int) Setting::where('key', 'LE')->value('value');

      $wallet = (int) DriverProfile::where('user_id', $driverId)->value('wallet');

      if ($wallet > - (20 * $LE)) {
        $this->drivers->updateStatus($driverId, 'approved');
      }

      return DriverProfile::where('user_id', $driverId)->get(['id', 'user_id', 'wallet', 'status']);
    });
  }

   public function getWallet(int $userId): float
    {
        return $this->drivers->getWalletByUserId($userId);
    }
}
