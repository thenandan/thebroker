<?php

namespace TheNandan\TheBroker;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use TheNandan\TheLogger\Http\Middleware\TheRequestLogger;

class TheBrokerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        /*
         * Register the migrations
         */
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * @throws BindingResolutionException
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/thelogger.php', 'thelogger');

        $this->registerMiddleware();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        //
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        //
    }

    /**
     * @throws BindingResolutionException
     */
    private function registerMiddleware()
    {
        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(TheRequestLogger::class);
    }
}
