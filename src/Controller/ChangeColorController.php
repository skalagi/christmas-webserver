<?php

namespace Syntax\Controller;

use Ratchet\ConnectionInterface;
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
        $this->queue->push($changeColor);
    }
}