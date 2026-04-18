<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class AppServiceProvider extends ServiceProvider
{

use Kreait\Firebase\Factory;

public function register()
{
    $this->app->singleton('firebase.messaging', function () {

        $credentialsJson = env('FIREBASE_CREDENTIALS_JSON');

        if ($credentialsJson) {
            return (new Factory)
                ->withServiceAccount(json_decode($credentialsJson, true))
                ->createMessaging();
        }

        $credentialsPath = env('FIREBASE_CREDENTIALS');

        if ($credentialsPath && file_exists($credentialsPath)) {
            return (new Factory)
                ->withServiceAccount($credentialsPath)
                ->createMessaging();
        }

        \Log::error('Firebase credentials not found');

        return null;
    });
}
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
