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

      $data = ["user_id" => $driverId, "employee_id" => Auth::id(), "amount" => $amount];
      WalletTransaction::create($data);

      $rows = $this->unCollectedForDriver($driverId);

      foreach ($rows as $row) {
        if ($amount <= 0) {
          break;
        }

        if ($amount >= $row->amount) {
          $amount -= $row->amount;
          $this->markCollected($row->id);
        }
      }

      $LE = (float) Setting::where('key', 'LE')->value('value');

      $wallet = (float) DriverProfile::where('id', $driverId)->value('wallet');

      if ($wallet > - (20 * $LE)) {
        $this->drivers->updateStatus($driverId, 'accepted');
      }

      return DriverProfile::where('user_id', $driverId)->get(['id', 'user_id', 'wallet', 'status']);
    });
  }

  public function unCollectedForDriver(int $driverId)
  {
    return CompanyCommission::where('user_id', $driverId)
      ->where('is_collected', false)
      ->orderBy('created_at')
      ->get();
  }

  public function markCollected(int $id): void
  {
    CompanyCommission::where('id', $id)
      ->update(['is_collected' => true]);
  }
}
