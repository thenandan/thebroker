<?php

namespace TheNandan\TheBroker\Queues;

use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Ramsey\Uuid\Uuid;
use TheNandan\TheBroker\Jobs\RabbitMQ as Job;

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
    private $message;
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
    }

    /**
     * @inheritDoc
     */
    public function size($queue = null)
    {
        return ($this->getChannel()->basic_get($queue)->delivery_info['message_count'] ?? 0) + 1;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function push($job, $data = '', $queue = null)
    {
        $this->declareQueue($queue);
        return $this->pushRaw($this->createPayload($job, $queue ?: $this->default, $data), $queue);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->declareQueue($queue);
        return $this->getChannel()->basic_publish(
            $this->message($payload),
            '',
            $queue
        );
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        $this->declareQueue($queue);
        return $this->laterRaw($delay, $this->createPayload($job, $queue, $data), $queue);
    }

    /**
     * Push a raw job onto the queue after a delay.
     *
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @param string $payload
     * @param string|null $queue
     * @return mixed
     * @throws \Exception
     */
    protected function laterRaw($delay, $payload, $queue = null)
    {
        $this->declareQueue($queue);
        return $this->getChannel()->basic_publish(
            $this->message($payload, $delay),
            '',
            $queue
        );
    }

    /**
     * @param $payload
     * @param null $delay
     * @return AMQPMessage
     * @throws \Exception
     */
    private function message($payload, $delay = null)
    {
        $settings = [
            'message_id' => Uuid::uuid4()->toString(),
            'delivery_mode' => 2,
        ];
        if (null !== $delay) {
            $settings['timestamp'] = $this->availableAt($delay);
        }
        $this->message = new AMQPMessage($payload, $settings);
        return $this->message;
    }

    /**
     * @inheritDoc
     */
    public function pop($queue = null)
    {
        return new Job(
            $this->container,
            $this,
            $this->getNextJob($queue),
            $this->connectionName,
            $queue
        );
    }

    /**
     * @param $queue
     * @return mixed
     */
    private function getNextJob($queue)
    {
        $this->declareQueue($queue);
        $this->getChannel()->basic_qos(null, 1, null); // This forces that one worker reads one message at a time
        return $this->getChannel()->basic_get($queue);
    }

    /**
     * @return void
     */
    private function setChannel(): void
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
