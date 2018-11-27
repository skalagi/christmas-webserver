<?php

namespace Syntax\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Syntax\ChristmasContainer;
use Syntax\Exception\WSException;
use Syntax\Service\Initiator;
use Syntax\Service\Logger;
use Syntax\WebSocket\InMemory\Clients;


class MessageComponent implements MessageComponentInterface
{
    /**
     * @var Clients
     */
    private $clients;

    /**
     * @var ControllersDispatcher
     */
    private $controllers;

    /**
     * @var Initiator
     */
    private $initiator;

    /**
     * MessageComponent constructor.
     * @param Clients $clients
     * @param ControllersDispatcher $controllers
     * @param Initiator $initiator
     */
    public function __construct(Clients $clients, ControllersDispatcher $controllers, Initiator $initiator)
    {
        $this->clients = $clients;
        $this->controllers = $controllers;
        $this->initiator = $initiator;
    }

    /**
     * @param ConnectionInterface $conn
     * @throws \Exception
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->_add($conn);

        $this->initiator->init($conn);
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $input = json_decode($msg, JSON_OBJECT_AS_ARRAY);
            $controller = $this->controllers->findController($input);
            $controller->execute(
                isset($input['value']) ? $input['value'] : [],
                $from
            );
        } catch(\Exception $e) {
            $from->send(
                json_encode([
                    'reason' => $e->getMessage(),
                    'type' => get_class($e)
                ]
            ));
            ChristmasContainer::getLogger()->addLog(Logger::EXCEPTION, $e->getMessage().PHP_EOL.$e->getTraceAsString(), null, null);

            if($e instanceof WSException && $e->closeConnection) {
                $from->close();
            }
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @throws \Exception
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->_remove($conn);
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     * @throws \Exception
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        ChristmasContainer::getLogger()->addLog(Logger::EXCEPTION, $e->getMessage().PHP_EOL.$e->getTraceAsString(), null, null);
    }
}