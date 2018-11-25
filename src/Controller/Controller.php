<?php

namespace Syntax\Controller;

use Ratchet\ConnectionInterface;
use Syntax\Service\Queue;
use Syntax\WebSocket\InMemory\Clients;

abstract class Controller
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Clients
     */
    protected $clients;

    /**
     * Controller constructor.
     * @param Queue $queue
     * @param Clients $clients
     */
    public function __construct(Queue $queue, Clients $clients)
    {
        $this->queue = $queue;
        $this->clients = $clients;
    }

    /**
     * @param array $input
     * @param ConnectionInterface $from
     * @return array
     */
    abstract public function execute($input, ConnectionInterface &$from);
}