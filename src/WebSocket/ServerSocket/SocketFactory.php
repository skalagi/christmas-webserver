<?php

namespace Syntax\WebSocket\ServerSocket;

use React\EventLoop\LoopInterface;
use React\Socket\Server as Socket;
use Syntax\ChristmasContainer;
use Syntax\Service\Logger;

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
     * @throws \Exception
     */
    public function createSocket(LoopInterface $loop)
    {
        $sock = new Socket($this->host.':'.$this->port, $loop);

        ChristmasContainer::getLogger()->addLog(Logger::SOCKET, 'Created socket on '.$this->host.':'.$this->port, null, null);
        return $sock;
    }
}