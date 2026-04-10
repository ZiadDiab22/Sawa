<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class AppServiceProvider extends ServiceProvider
{


public function register()
{
    $this->app->singleton('firebase.messaging', function () {

        $credentialsPath = env('FIREBASE_CREDENTIALS'); 

        if (!$credentialsPath || !file_exists($credentialsPath)) {
            \Log::error('Firebase credentials file not found', [
                'path' => $credentialsPath
            ]);
            return null;
        }

        return (new \Kreait\Firebase\Factory)
            ->withServiceAccount($credentialsPath)
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
