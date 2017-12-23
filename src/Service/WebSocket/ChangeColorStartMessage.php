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

    /**
     * @var array 
     */
    private $_INIT_DEFAULT_EMPTY_COLOR = [0, 0, 0];

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
                'rgb' => $this->queue->getLastChange() ? $this->queue->getLastChange()->rgb : $this->_INIT_DEFAULT_EMPTY_COLOR
            ]
        ]))->_toJSON());
    }
}