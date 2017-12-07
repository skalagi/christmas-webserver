<?php

namespace Syntax\Controller;

use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use Syntax\Exception\AVRException;
use Syntax\Exception\ChangeStateException;
use Syntax\Model\Application\LogEntity;
use Syntax\Model\Application\LogEvents;
use Syntax\Model\Transport\ChangeStateBroadcast;
use Syntax\Model\Transport\ControllerInput\ChangeState;
use Syntax\Model\Transport\Error;
use Syntax\Service\Database;
use Syntax\WebSocket\InMemory\Clients;
use Syntax\WebSocket\InMemory\Lights;
use Syntax\WebSocket\ControllerInterface;

class ChangeStateController implements ControllerInterface
{

    /**
     * @var Lights
     */
    protected $lights;

    /**
     * @var bool
     */
    protected $busy = false;

    /**
     * @var Clients
     */
    protected $clients;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var Database
     */
    private $database;

    const BUSY_LIMIT_PER_USER = 15; // max 60
    const WORKER_TIMEOUT = .5;

    /**
     * LightsController constructor.
     * @param Lights $lights
     * @param Clients $clients
     * @param LoopInterface $loop
     * @param Database $database
     */
    public function __construct(Lights $lights, Clients $clients, LoopInterface $loop, Database $database)
    {
        $this->lights = $lights;
        $this->clients = $clients;
        $this->loop = $loop;
        $this->database = $database;
    }

    /**
     * @param ChangeState $input
     * @param ConnectionInterface $from
     * @return array
     * @throws ChangeStateException
     */
    public function execute($input,  ConnectionInterface &$from)
    {
        if($this->busy) {
            /** @noinspection PhpUndefinedFieldInspection */
            if($this->busy == $from->resourceId) {
                /** @noinspection PhpUndefinedFieldInspection */
                $this->logBusyError($from->resourceId);
            }

            /** @noinspection PhpUndefinedFieldInspection */
            if($this->checkIfShouldBan($from->resourceId)) {
                $exception = new ChangeStateException('Operations limit reached.. Client kicked!');
                $exception->closeConnection = true;
            }
            throw isset($exception) ? $exception : new ChangeStateException('Server is currently busy!');
        }

        try {
            /** @noinspection PhpUndefinedFieldInspection */
            $this->busy = (int)$from->resourceId;

            $this->lights->changeState($input->identity, $input->state);
            $this->clients->broadcastMessage(new ChangeStateBroadcast([
                'value' => [
                    'identity' => $input->identity,
                    'state' => $input->state
                ]
            ]), $from);

            /** @noinspection PhpUndefinedFieldInspection */
            $this->logChangeState((int)$from->resourceId, $from->remoteAddress);

            $this->loop->addTimer(self::WORKER_TIMEOUT, function() {
                $this->busy = false;
            });

            return array_merge($input->getFields(), [
                'controllerResponse' => 'OK'
            ]);
        } catch(AVRException $e) {
            $this->responseAVRError($from, $e->getMessage());
            return array_merge($input->getFields(), [
                'controllerResponse' => 'ERR'
            ]);
        }
    }

    /**
     * @param $resourceId
     * @return bool
     */
    private function checkIfShouldBan($resourceId)
    {
        $lastBusyTimeLimiter = new \DateTime();
        $lastBusyTimeLimiter->modify('-'.static::BUSY_LIMIT_PER_USER.' seconds');
        $lastBusy = count($this->database->selectQuery('WHERE `data` LIKE "%'.$resourceId.'%" AND `created` > "'.$lastBusyTimeLimiter->format('Y-m-d H:i:s').'" AND `name` = "'.LogEvents::BUSY_ERROR.'"'));
        if($lastBusy > static::BUSY_LIMIT_PER_USER+((int)static::BUSY_LIMIT_PER_USER/4)) {
            return true;
        }

        return false;
    }

    /**
     * @param ConnectionInterface $conn
     * @param $message
     */
    private function responseAVRError(ConnectionInterface $conn, $message)
    {
        $error = new Error();
        $error->reason = $message;
        $error->type = AVRException::class;
        $conn->send($error->_toJSON());
    }

    /**
     * @param int $resourceId
     */
    private function logBusyError($resourceId)
    {
        $log = new LogEntity();
        $log->createdTime = new \DateTime();
        $log->initiator = __CLASS__.': '.__LINE__;
        $log->name = LogEvents::BUSY_ERROR;
        $log->data['rid'] = $resourceId;
        $this->database->addLog($log);
    }

    /**
     * @param int $resourceId
     * @param string $ipAddress
     */
    private function logChangeState($resourceId, $ipAddress)
    {
        $log = new LogEntity();
        $log->createdTime = new \DateTime();
        $log->initiator = __CLASS__.': '.__LINE__;
        $log->name = LogEvents::CHANGE_STATE_CONTROLLER;
        $log->data['rid'] = $resourceId;
        $log->data['ip'] = $ipAddress;
        $this->database->addLog($log);
    }
}
