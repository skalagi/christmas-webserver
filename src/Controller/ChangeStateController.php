<?php

namespace Syntax\Controller;

use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use Syntax\Exception\AVRException;
use Syntax\Exception\ChangeStateException;
use Syntax\Model\Application\LogEntity;
use Syntax\Model\Application\LogEvents;
use Syntax\Model\Transport\Broadcast\ChangeStateBroadcast;
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
        try {
            $this->lights->changeState($input->identity, $input->state, function() use($input, $from) {
                $this->clients->broadcastMessage(new ChangeStateBroadcast([
                    'value' => [
                        'identity' => $input->identity,
                        'state' => $input->state
                    ]
                ]), null);
                
                 /** @noinspection PhpUndefinedFieldInspection */
                $this->logChangeState((int)$from->resourceId, $from->httpRequest->getHeader('X-Forwarded-For')[0], $input->identity, $input->state);
            }, function() use($from) {
                $error = new Error();
                $error->reason = 'AVR controller is overloaded!';
                $error->type = \Syntax\Exception\AVRBusyException::class;
                $from->send($error->_toJSON());
            });

        } catch(AVRException $e) {
            $error = new Error();
            $error->reason = $e->getMessage();
            $error->type = get_class($e);
            return $error->getFields();
        }
    }

    /**
     * @param int $resourceId
     * @param string $ipAddress
     * @param int $channel
     * @param bool $state
     */
    private function logChangeState($resourceId, $ipAddress, $channel, $state)
    {
        $log = new LogEntity();
        $log->createdTime = new \DateTime();
        $log->initiator = __CLASS__.': '.__LINE__;
        $log->name = LogEvents::CHANGE_STATE_CONTROLLER;
        $log->data['rid'] = $resourceId;
        $log->data['ip'] = $ipAddress;
        $log->data['channel'] = $channel;
        $log->data['state'] = $state;
        $this->database->addLog($log);
    }
}
