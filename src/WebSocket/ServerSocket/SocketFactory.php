<?php

namespace Syntax\WebSocket\ServerSocket;

use React\EventLoop\LoopInterface;
use React\Socket\Server as Socket;

class SocketFactory
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * SocketFactory constructor.
     * @param $bindHost
     * @param $bindPort
     */
    public function __construct($bindHost, $bindPort)
    {
        $this->host = $bindHost;
        $this->port = $bindPort;
    }

    /**
     * @param LoopInterface $loop
     * @return Socket
     * @throws \React\Socket\ConnectionException
     */
    public function createSocket(LoopInterface $loop)
    {
        $sock = new Socket($loop);
        $sock->listen($this->port, $this->host);

        return $sock;
    }
}