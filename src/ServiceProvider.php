<?php
namespace MyLukin\IP2City;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use MyLukin\IP2City\IP2City;

class ServiceProvider extends LaravelServiceProvider
{

    /**
     * Boot the provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path('ip2city.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('ip2city', function ($app) {
            return IP2City::instance($app['config']->get('ip2city.datfile'));
        });
    }

}
