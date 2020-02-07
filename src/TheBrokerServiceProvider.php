<?php

namespace TheNandan\TheBroker;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use TheNandan\TheBroker\Connectors\RabbitMQConnector;
use TheNandan\TheLogger\Http\Middleware\TheRequestLogger;

class TheBrokerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function boot()
    {
        $manager = $this->app->make('queue');
        $this->registerRebbitMQConnector($manager);
    }

    /**
     *
     */
    public function register()
    {

    }

    /**
     * Register the Amazon RabbitMQ queue connector.
     *
     * @param  QueueManager  $manager
     * @return void
     */
    protected function registerRebbitMQConnector($manager)
    {
        $manager->addConnector('rabbitmq', function () {
            return new RabbitMQConnector;
        });
    }
}
