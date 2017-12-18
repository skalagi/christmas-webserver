<?php

namespace Syntax\Admin\Commands;

use Symfony\Component\Console\Output\OutputInterface;
use Syntax\Admin\CommandInterface;
use Syntax\Model\Transport\Broadcast\ServerRestartBroadcast;
use Syntax\WebSocket\InMemory\Clients;

class Shutdown implements CommandInterface
{
    /**
     * @var Clients
     */
    private $clients;

    /**
     * Shutdown constructor.
     * @param Clients $clients
     */
    public function __construct(Clients $clients)
    {
        $this->clients = $clients;
    }

    /**
     * @param OutputInterface $output
     */
    public function exec(OutputInterface $output)
    {
        $this->clients->broadcastMessage((new ServerRestartBroadcast())->_toJSON());

        exit;
    }
}