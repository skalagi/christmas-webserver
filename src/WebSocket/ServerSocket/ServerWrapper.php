<?php

namespace Syntax\WebSocket\ServerSocket;

use React\EventLoop\LoopInterface;
use React\Socket\Server as Socket;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Syntax\WebSocket\MessageComponent;

class ServerWrapper
{
    /**
     * @var IoServer
     */
    private $server;

    /**
     * ServerWrapper constructor.
     * @param LoopInterface $loop
     * @param Socket $socket
     * @param \Syntax\WebSocket\MessageComponent $messageComponent
     */
    public function __construct(LoopInterface $loop, Socket $socket, MessageComponent $messageComponent)
    {
        $this->server = new IoServer(
            new HttpServer(
                new WsServer($messageComponent)
            ),
            $socket,
            $loop
        );
    }

    /**
     * Run server
     */
    public function run()
    {
        $this->server->run();
    }
}