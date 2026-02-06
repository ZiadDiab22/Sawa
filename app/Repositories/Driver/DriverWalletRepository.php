<?php

namespace App\Repositories\Driver;

use App\Models\DriverProfile;
use App\Models\WalletTransaction;
use \Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DriverWalletRepository
{
    public function getDriverWallet(int $userId): float
    {
        return (float)DriverProfile::query()->where('user_id', $userId)->value('wallet');
    }

    public function getTransactions(int $userId): Collection
    {
        return WalletTransaction::query()->where('user_id', $userId)->latest()->get();
    }
}
