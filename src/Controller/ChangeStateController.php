<?php

namespace Syntax\Controller;

use Ratchet\ConnectionInterface;
use Syntax\Exception\QueueException;
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
        $this->_checkQueuePreviousItemIsTheSame($changeState);

        $changeState->status = AbstractModelWithStatus::STATUS_ADDED;
        $this->queue->push($changeState);
        $this->clients->broadcastMessage(json_encode($changeState));
    }

    /**
     * @param ChangeState $changeState
     * @throws QueueException
     */
    private function _checkQueuePreviousItemIsTheSame(ChangeState $changeState)
    {
        $items = $this->queue->getItems();
        if(!$items) {
            return;
        }

        $lastItem = $items[count($items)-1];
        if($lastItem instanceof ChangeState && $lastItem->state == $changeState->status && $lastItem->channel == $changeState->channel) {
            throw new QueueException('Your change relay state was ignored cause previous state is the same!');
        }
    }
}
