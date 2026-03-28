<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class AppServiceProvider extends ServiceProvider
{


public function register()
{
    $this->app->singleton('firebase.messaging', function () {

        $credentials = env('FIREBASE_CREDENTIALS_JSON');

        if (!$credentials) {
            \Log::error('Firebase credentials not found in ENV');
            return null;
        }

        $factory = (new Factory)->withServiceAccount(json_decode($credentials, true));

        return $factory->createMessaging();
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
