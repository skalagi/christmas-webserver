<?php

namespace Syntax\Service\WebSocket;

use Ratchet\ConnectionInterface;
use Syntax\Model\Transport\Broadcast\ChangeColorBroadcast;use Syntax\WebSocket\InMemory\ColorChangesQueue;

class ChangeColorStartMessage
{
    /**
     * @var ColorChangesQueue
     */
    private $queue;

    const INIT_DEFAULT_EMPTY_COLOR = '000000';

    /**
     * ChangeColorStartMessage constructor.
     * @param ColorChangesQueue $queue
     */
    public function __construct(ColorChangesQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function sendCurrentColor(ConnectionInterface $connection)
    {
        $connection->send((new ChangeColorBroadcast([
            'value' => [
                'hex' => $this->queue->getLastChange() ? $this->queue->getLastChange()->hex : self::INIT_DEFAULT_EMPTY_COLOR
            ]
        ]))->_toJSON());
    }
}