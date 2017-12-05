<?php

if(!isset($argv[1])) {
    throw new \Exception('Required port as second argument!');
}

require __DIR__.'/../../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Fake implements MessageComponentInterface
{
    function onOpen(ConnectionInterface $conn)
    {
    }

    function onClose(ConnectionInterface $conn)
    {
    }

    function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
    }

    function onMessage(ConnectionInterface $from, $msg)
    {
        echo $msg.PHP_EOL;
    }
}



$server = IoServer::factory(new Fake(), $argv[1]);
$server->run();