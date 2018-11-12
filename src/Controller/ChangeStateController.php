<?php

namespace Syntax\Controller;

use Ratchet\ConnectionInterface;
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
        $this->queue->push($changeState);
    }
}
