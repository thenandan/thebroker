<?php

namespace TheNandan\TheBroker\Queues;

use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQ
 *
 * @package TheNandan\TheBroker\Queues
 */
class RabbitMQ extends Queue implements QueueContract
{
    private $connection;
    private $channel;
    private $default;
    private $retryAfter;

    /**
     * RabbitMQ constructor.
     *
     * @param AMQPStreamConnection $connection
     * @param $default
     * @param $retryAfter
     */
    public function __construct(AMQPStreamConnection $connection, $default, $retryAfter)
    {
        $this->connection = $connection;
        $this->default = $default;
        $this->retryAfter = $retryAfter;
    }

    /**
     * @inheritDoc
     */
    public function size($queue = null)
    {
        dd('dd in size, queue: '.$queue);
    }

    /**
     * @inheritDoc
     */
    public function push($job, $data = '', $queue = null)
    {
        $this->declareQueue($queue);
        return $this->pushRaw($this->createPayload($job, $queue ?: $this->default, $data), $queue);
    }

    /**
     * @inheritDoc
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->declareQueue($queue);
        $this->getChannel()->basic_publish(
          new AMQPMessage($payload),
            '',
            $queue
        );
    }

    /**
     * @inheritDoc
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        $this->declareQueue($queue);
        return $this->laterRaw($delay, $this->createPayload($job, $queue, $data), $queue);
    }

    /**
     * @inheritDoc
     */
    public function pop($queue = null)
    {
        dd('dd in later', $queue);
    }

    /**
     *
     */
    private function setChannel()
    {
        $this->channel = $this->connection->channel();
    }

    /**
     * @return mixed
     */
    private function getChannel(): AMQPChannel
    {
        if (null === $this->channel) {
            $this->setChannel();
        }
        return $this->channel;
    }

    /**
     * @param $queue
     */
    public function declareQueue($queue)
    {
        $this->getChannel()->queue_declare($queue, false, true, false, false);
    }
}
