<?php

namespace TheNandan\TheBroker\Connectors;

use Illuminate\Queue\Connectors\ConnectorInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use TheNandan\TheBroker\Queues\RabbitMQ;

/**
 * Class RabbitMQConnector
 *
 * @package TheNandan\TheBroker\Connectors
 */
class RabbitMQConnector implements ConnectorInterface
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function connect(array $config)
    {
        return new RabbitMQ(
            AMQPStreamConnection::create_connection($config['hosts']),
            $config['queue'] ?? 'default',
            $config['retry_after'] ?? null
        );
    }



}
