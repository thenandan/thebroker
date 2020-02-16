<?php

namespace TheNandan\TheBroker\Jobs;

use Illuminate\Container\Container;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Contracts\Queue\Job as JobContract;
use TheNandan\TheBroker\Queues\RabbitMQ as Queue;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQ
 *
 * @package TheNandan\TheBroker\Jobs
 */
class RabbitMQ extends Job implements JobContract
{
    protected $job;
    protected $rabbitmq;
    protected $properties = [];

    /**
     * RabbitMQ constructor.
     * @param Container $container
     * @param Queue $rabbitmq
     * @param $job
     * @param $connectionName
     * @param $queue
     */
    public function __construct(Container $container, Queue $rabbitmq, $job, $connectionName, $queue)
    {
        $this->job = $job;
        $this->queue = $queue;
        $this->rabbitmq = $rabbitmq;
        $this->container = $container;
        $this->connectionName = $connectionName;
        if ($this->job instanceof AMQPMessage) {
            $this->properties =  $this->job->get_properties();
        }
    }

    /**
     * @inheritDoc
     */
    public function getJobId()
    {
        return $this->properties['message_id'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getRawBody()
    {
        if ($this->job instanceof AMQPMessage) {
            return $this->job->getBody();
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function attempts()
    {
        return $this->properties['attempts'] ?? 0;
    }

    public function delete()
    {
        if ($this->job instanceof AMQPMessage) {
            $this->job->delivery_info['channel']->basic_ack($this->job->delivery_info['delivery_tag']);
            $this->deleted = true;
        }
    }
}
