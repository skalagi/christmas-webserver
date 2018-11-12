<?php

namespace Syntax\Controller;

use Ratchet\ConnectionInterface;
use Syntax\Service\Queue;

abstract class Controller
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * Controller constructor.
     * @param Queue $queue
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param array $input
     * @param ConnectionInterface $from
     * @return array
     */
    abstract public function execute($input, ConnectionInterface &$from);
}