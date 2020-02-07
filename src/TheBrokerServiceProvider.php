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

    }

    /**
     * 
     */
    public function register()
    {

    }
}
