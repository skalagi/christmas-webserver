<?php

namespace Syntax\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Syntax\Exception\WSException;
use Syntax\Model\Transport\Error;
use Syntax\Service\Stats;
use Syntax\Service\Database;
use Syntax\WebSocket\InMemory\Clients;


class MessageComponent implements MessageComponentInterface
{
    /**
     * @var Clients
     */
    private $clients;

    /**
     * @var Database
     */
    private $database;

    /**
     * @var Stats
     */
    private $stats;

    /**
     * @var ControllersDispatcher
     */
    private $controllers;

    /**
     * MessageComponent constructor.
     * @param Clients $clients
     * @param Stats $stats
     * @param Database $database
     * @param ControllersDispatcher $dispatcher
     */
    public function __construct(Clients $clients, Stats $stats, Database $database, ControllersDispatcher $dispatcher)
    {
        $this->clients = $clients;
        $this->database = $database;
        $this->stats = $stats;
        $this->controllers = $dispatcher;
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->stats->addCurrentOnline();
        /** @noinspection PhpUndefinedFieldInspection */
        $this->stats->addOpenConnection($conn->resourceId, $conn->remoteAddress);
        $this->clients->_add($conn);

        $this->stats->_sendStats();
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $input = json_decode($msg, JSON_OBJECT_AS_ARRAY);
            $controller = $this->controllers->findController($input);
            $response = $controller->execute(
                $this->controllers->prepareInput($input),
                $from
            );
            $from->send($response->_toJSON());
        } catch(\Exception $e) {
            $from->send(json_encode([
                'error' => new Error([
                    'reason' => $e->getMessage(),
                    'type' => get_class($e)
                ])
            ]));

            $this->onError($from, $e);

            if($e instanceof WSException && $e->closeConnection) {
                $from->close();
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->_remove($conn);
        $this->stats->removeCurrentOnline();
        /** @noinspection PhpUndefinedFieldInspection */
        $this->stats->addCloseConnection($conn->resourceId, $conn->remoteAddress);
        $this->stats->_sendStats();
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->stats->addMCError($e->getTrace(), $e->getMessage());
    }
}