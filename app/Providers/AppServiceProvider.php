<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class AppServiceProvider extends ServiceProvider
{

public function register()
{
    $this->app->singleton('firebase.messaging', function () {

        $credentialsPath = config('firebase.projects.app.credentials');

        if (!$credentialsPath || !file_exists($credentialsPath)) {
            \Log::error('Firebase credentials not found', [
                'path' => $credentialsPath
            ]);
            return null;
        }

        return (new Factory)
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
