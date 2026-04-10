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
        if (!$credentialsPath || !file_exists($credentialsPath)) {
            \Log::error('Firebase credentials file not found', [
                'path' => $credentialsPath
            ]);
            return null;
        }

        return (new Factory)
            ->withServiceAccount(json_decode($credentials, true)) 
            ->createMessaging();
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
