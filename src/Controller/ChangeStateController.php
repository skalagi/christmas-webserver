<?php

namespace Syntax\Controller;

use Ratchet\ConnectionInterface;
use Syntax\Model\AbstractModelWithStatus;
use Syntax\Model\ChangeState;


class ChangeStateController extends Controller
{
    /**
     * @param array $input
     * @param ConnectionInterface $from
     * @throws \Exception
     */
    public function execute($input,  ConnectionInterface &$from)
    {
        $changeState = new ChangeState($input);
        $changeState->status = AbstractModelWithStatus::STATUS_ADDED;
        $this->queue->push($changeState);
        $this->clients->broadcastMessage(json_encode($changeState));
    }
}
