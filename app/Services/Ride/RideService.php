<?php

namespace App\Services\Ride;

use App\Events\RideCancelledByDriver;
use App\Events\RideCancelledByUser;
use App\Events\RideCompleted;
use App\Events\RideStarted;
use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Repositories\DriverRepository;
use App\Repositories\Ride\ProfitRepository;
use App\Repositories\Ride\RideRepository;
use App\Repositories\Ride\RideStatusHistoryRepository;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class RideService
{
    public function __construct(
        private RideRepository              $rides,
        private RideStatusHistoryRepository $histories,
        private ProfitRepository            $profits,
        private DriverRepository            $drivers,
    )
    {
    }

    public function start(int $rideId, int $driverId)
    {
        return DB::transaction(function () use ($rideId, $driverId) {

            $ride = $this->rides->findForDriver($rideId, $driverId);

            if (!$ride) {
                throw new \Exception('unauthorized - this ride for another driver');
            }

            if ($ride->status !== 'driver_on_way') {
                throw new \Exception('Ride cannot be started');
            }

            $oldStatus = $ride->status;

            $this->rides->updateStatus($ride, 'on_going');

            $this->histories->create(
                $ride->id,
                $oldStatus,
                'on_going',
                'driver',
                $driverId
            );

            broadcast(new RideStarted($ride))->toOthers();

            return $this->set($ride);
        });
    }

    public function set(Ride $ride)
    {
        $ride->refresh()->load('user:id,name,phone');

        $ride->setAttribute('user_name', $ride->user->name);
        $ride->setAttribute('user_phone', $ride->user->phone);

        unset($ride->user);

        return $ride;
    }

    public function complete(array $data, int $driverId)
    {
        return DB::transaction(function () use ($data, $driverId) {

            $ride = $this->rides->findForDriver($data['ride_id'], $driverId);

            if (!$ride) {
                throw new \Exception('unauthorized - this ride for another driver');
            }

            if ($ride->status !== 'on_going') {
                throw new \Exception('Ride cannot be completed');
            }

            if ($ride->code !== $data['code']) {
                throw new \Exception('Invalid code');
            }

            $oldStatus = $ride->status;

            $this->rides->updateStatus($ride, 'completed');

            DriverProfile::query()->where('user_id', $driverId)->update(['has_ride' => false]);

            $this->histories->create(
                $ride->id,
                $oldStatus,
                'completed',
                'driver',
                $driverId
            );

            $commissionPercent = (float)Setting::where('key', 'company_commission_percentage')->value('value');
            $LE = (int)Setting::where('key', 'LE')->value('value');

            $companyAmount = $ride->price * ($commissionPercent / 100);
            $driverAmount = $ride->price - $companyAmount;

            $this->drivers->decrementWallet($driverId, $companyAmount);

            $this->profits->createDriverProfit($driverId, $ride->id, $driverAmount);
            $this->profits->createCompanyCommission($driverId, $ride->id, $companyAmount);

            $wallet = (int)DriverProfile::where('user_id', $driverId)->value('wallet');

            if ($wallet <= -(20 * $LE)) {
                $this->drivers->updateStatus($driverId, 'suspended');
                $driver = User::find($driverId);
                $firebaseResult = NotificationService::send(
                $driver->id,
                'driver_suspended_debt',
                'تم تعليق حسابك ⚠️',
                'لقد تجاوزت الحد المسموح به للدين. تم تعليق حسابك حتى تسديد المبلغ المستحق.',
        [
            'debt_limit' => (string)(20 * $LE),
            'current_wallet' => (string)$wallet
        ]
    );
            }

            WalletTransaction::query()->create([
                'user_id' => $driverId,
                'ride_id' => $ride->id,
                'type' => 'debit',
                'reason' => 'ride_commission',
                'amount' => $companyAmount,
            ]);

            broadcast(new RideCompleted($ride))->toOthers();

            return $this->set($ride);
        });
    }

    public function userCancel(int $id, int $userId)
    {
        return DB::transaction(function () use ($id, $userId) {

            $ride = $this->rides->findForUser($id, $userId);

            if (!$ride) {
                throw new \Exception('This ride isnt for you or not exist');
            }

            if ($ride->status != 'driver_on_way') {
                throw new \Exception('This ride cannot be cancelled in this status');
            }

            $oldStatus = $ride->status;

            $this->rides->updateStatus($ride, 'cancelled');

            DriverProfile::query()->where('user_id', $ride->driver_id)->update(['has_ride' => false]);

            $this->histories->create(
                $ride->id,
                $oldStatus,
                'cancelled',
                'passenger',
                $userId
            );

            broadcast(new RideCancelledByUser($ride))->toOthers();

            return $ride->refresh();
        });
    }

    public function driverCancel(int $id, int $driverId)
    {
        return DB::transaction(function () use ($id, $driverId) {

            $ride = $this->rides->findForDriver($id, $driverId);

            if (!$ride) {
                throw new \Exception('This ride isnt for you or not exist');
            }

            if ($ride->status != 'driver_on_way') {
                throw new \Exception('This ride cannot be cancelled in this status');
            }

            $oldStatus = $ride->status;

            $this->rides->updateStatus($ride, 'cancelled');

            $this->histories->create(
                $ride->id,
                $oldStatus,
                'cancelled',
                'driver',
                $driverId
            );

            DriverProfile::query()->where('user_id', $driverId)->update(['has_ride' => false]);

            $fee = (float)DB::table('settings')->where('key', 'cancelling_ride_fee')->value('value');

            if ($fee > 0) {
                $this->deductCancellationFee($driverId, $fee);
                $this->profits->createDriverProfit($driverId, $ride->id, -($fee));
                WalletTransaction::query()->create([
                    'user_id' => $driverId,
                    'ride_id' => $ride->id,
                    'type' => 'debit',
                    'reason' => 'cancellation_penalty',
                    'amount' => $fee,
                ]);
            }
            broadcast(new RideCancelledByDriver($ride))->toOthers();

            return $ride->refresh();
        });
    }

    public function deductCancellationFee(int $driverId, float $fee): void
    {
        DB::transaction(function () use ($driverId, $fee) {

            DB::table('driver_profiles')
                ->where('user_id', $driverId)
                ->decrement('wallet', $fee);
        });
    }
}
