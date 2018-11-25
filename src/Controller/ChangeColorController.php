<?php

namespace Syntax\Controller;

use Ratchet\ConnectionInterface;
use Syntax\Model\AbstractModelWithStatus;
use Syntax\Model\ChangeColor;

class ChangeColorController extends Controller
{
    /**
     * @param array $input
     * @param ConnectionInterface $from
     * @return array|void
     * @throws \Exception
     */
    public function execute($input, ConnectionInterface &$from)
    {
        $changeColor = new ChangeColor($input);
        $changeColor->status = AbstractModelWithStatus::STATUS_ADDED;
        $this->queue->push($changeColor);
        $this->clients->broadcastMessage(json_encode($changeColor));
    }
}