<?php

namespace Syntax\Service\WebSocket;


use Ratchet\ConnectionInterface;
use Syntax\WebSocket\InMemory\Lights;
use Syntax\Model\Transport\ChangeStateBroadcast;

class ChangeStateStartMessage
{
    /**
     * @var Lights
     */
    private $lights;

    /**
     * ChangeStateStartMessage constructor.
     * @param Lights $lights
     */
    public function __construct(Lights $lights)
    {
        $this->lights = $lights;
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function sendCurrentStates(ConnectionInterface $connection)
    {
        foreach($this->lights->states() as $identity => $state) {
            $connection->send((new ChangeStateBroadcast([
                'value' => [
                    'identity' => $identity,
                    'state' => $state
                ]
            ]))->_toJson());
        }
    }
}