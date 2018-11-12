<?php

namespace Syntax\Service\Executor;

use Syntax\WebSocket\InMemory\Clients;

abstract class AbstractExecutor
{
    /**
     * @var Clients
     */
    protected $clients;

    /**
     * @return Clients
     */
    public function getClients(): Clients
    {
        return $this->clients;
    }

    /**
     * @param Clients $clients
     */
    public function setClients(Clients $clients): void
    {
        $this->clients = $clients;
    }

    /**
     * @param $data
     */
    abstract public function execute($data);
}