<?php

namespace Rifalpg\GDriveDirect\Providers;

use Illuminate\Support\ServiceProvider;
use Rifalpg\GDriveDirect\Direct;

class GDriveDirectServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('GDriveDirect', function () {
            return new Direct();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

}
