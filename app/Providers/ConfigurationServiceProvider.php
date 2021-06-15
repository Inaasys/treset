<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Conf;

class ConfigurationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Configuration', function ($app) {
            return new Configuration();
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
