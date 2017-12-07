<?php

namespace Syntax\WebSocket;

use Ratchet\ConnectionInterface;
use Syntax\Model\Transport\AbstractTransportJSON;

interface ControllerInterface
{
    /**
     * @param AbstractTransportJSON $input
     * @param ConnectionInterface $from
     * @return AbstractTransportJSON
     */
    public function execute($input, ConnectionInterface &$from);
}