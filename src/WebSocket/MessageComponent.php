<?php

namespace Syntax\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Syntax\Admin\Input;
use Syntax\Exception\WSException;
use Syntax\Model\Transport\Error;
use Syntax\Service\LogDisplayer;
use Syntax\Service\Stats;
use Syntax\Service\Database;
use Syntax\Service\WebSocket\AVRService;
use Syntax\Service\WebSocket\ChangeColorStartMessage;
use Syntax\Service\WebSocket\ChangeStateStartMessage;
use Syntax\WebSocket\InMemory\Clients;
use Syntax\WebSocket\InMemory\ColorChangesQueue;


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
     * @var ChangeStateStartMessage
     */
    private $changeStateStartMessage;

    /**
     * @var ChangeColorStartMessage
     */
    private $changeColorStartMessage;

    /**
     * @var AVRService
     */
    private $avr;

    const LOGS_LIMIT = 50;

    /**
     * MessageComponent constructor.
     * @param Clients $clients
     * @param Stats $stats
     * @param Database $database
     * @param ControllersDispatcher $dispatcher
     * @param ChangeStateStartMessage $changeStateStartMessage
     * @param ChangeColorStartMessage $changeColorStartMessage
     * @param AVRService $avr
     * @param ColorChangesQueue $queue
     * @param LogDisplayer $logDisplayer
     * @param Input $adminInput
     */
    public function __construct(
        Clients $clients,
        Stats $stats,
        Database $database,
        ControllersDispatcher $dispatcher,
        ChangeStateStartMessage $changeStateStartMessage,
        ChangeColorStartMessage $changeColorStartMessage,
        AVRService $avr,
        ColorChangesQueue $queue,
        LogDisplayer $logDisplayer,
        Input $adminInput
    )
    {
        $this->clients = $clients;
        $this->database = $database;
        $this->stats = $stats;
        $this->avr = $avr;

        $this->controllers = $dispatcher;
        $this->changeStateStartMessage = $changeStateStartMessage;
        $this->changeColorStartMessage = $changeColorStartMessage;

        // init color changes queue
        $queue->queueLoopCall();

        // init log displayer
        $logDisplayer->startDisplaying(
            $this->database->selectQuery('ORDER BY `created_time` DESC LIMIT '.self::LOGS_LIMIT)
        );

        // init admin input
        $adminInput->init();
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->stats->addCurrentOnline();
        /** @noinspection PhpUndefinedFieldInspection */
        $this->stats->addOpenConnection($conn->resourceId, $conn->httpRequest->getHeader('X-Forwarded-For')[0]);
        $this->clients->_add($conn);

        $this->stats->_sendStats();
        $this->changeStateStartMessage->sendCurrentStates($conn);
        $this->changeColorStartMessage->sendCurrentColor($conn);
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
            if($response) {
                $from->send(json_encode($response));
            }
        } catch(\Exception $e) {
            $from->send(
                (new Error([
                    'reason' => $e->getMessage(),
                    'type' => get_class($e)
                ]))->_toJSON()
            );

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
        $this->stats->addCloseConnection($conn->resourceId, $conn->httpRequest->getHeader('X-Forwarded-For')[0]);
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