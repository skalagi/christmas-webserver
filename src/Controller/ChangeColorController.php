<?php

namespace Syntax\Controller;

use Ratchet\ConnectionInterface;
use Syntax\Exception\QueueException;
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
        $this->_checkQueuePreviousItemIsTheSame($changeColor);

        $changeColor->status = AbstractModelWithStatus::STATUS_ADDED;
        $this->queue->push($changeColor);
        $this->clients->broadcastMessage(json_encode($changeColor));
    }

    /**
     * @param ChangeColor $changeColor
     * @throws QueueException
     */
    private function _checkQueuePreviousItemIsTheSame(ChangeColor $changeColor)
    {
        $items = $this->queue->getItems();
        if(!$items) {
            return;
        }

        $lastItem = $items[count($items)-1];
        if($lastItem instanceof ChangeColor && $lastItem->r == $changeColor->r && $lastItem->g == $changeColor->g && $lastItem->b == $changeColor->b) {
            throw new QueueException('Your change color was ignored cause previous color is the same!');
        }
    }
}