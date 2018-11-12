<?php

namespace Syntax\WebSocket\InMemory;

use Ratchet\ConnectionInterface;
use Syntax\ChristmasContainer;
use Syntax\Model\Transport\AbstractTransportJSON;
use Syntax\Service\Logger;

class Clients implements \Iterator
{
    /**
     * @var array|ConnectionInterface[]
     */
    private $array = [];

    /**
     * @var int
     */
    private $index = 0;

    /**
     * Clients constructor.
     * @param array $clients
     */
    public function __construct(array $clients = [])
    {
        $this->array = $clients;
    }

    /**
     * @return ConnectionInterface
     */
    public function current()
    {
        return $this->array[$this->index];
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->index++;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->array[$this->index]);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * @param $index
     * @return null|ConnectionInterface
     */
    public function _get($index)
    {
        if(!isset($this->array[$index])) {
            return null;
        }

        return $this->array[$index];
    }

    /**
     * @return array|\Ratchet\ConnectionInterface[]
     */
    public function _all()
    {
        return $this->array;
    }

    /**
     * @param $index
     * @return bool
     */
    public function _exist($index)
    {
        return isset($this->array[$index]);
    }

    /**
     * @param ConnectionInterface $connection
     * @return $this
     * @throws \Exception
     */
    public function _add(ConnectionInterface $connection)
    {
        $this->array[] = $connection;
        /** @noinspection PhpUndefinedFieldInspection */
        $ip = $connection->httpRequest->getHeader('X-Forwarded-For') ? $connection->httpRequest->getHeader('X-Forwarded-For')[0] : $connection->remoteAddress;
        /** @noinspection PhpUndefinedFieldInspection */
        ChristmasContainer::getLogger()->addLog(Logger::CLIENT, 'New client connected', $ip, $connection->resourceId);
        return $this;
    }

    /**
     * @param ConnectionInterface $connection
     * @return ConnectionInterface|false
     * @throws \Exception
     */
    public function _remove(ConnectionInterface $connection)
    {
        foreach($this->array as $i => $connectionInternal) {
            if($connectionInternal == $connection) {
                $value = $connectionInternal;
                unset($this->array[$i]);
                /** @noinspection PhpUndefinedFieldInspection */
                $ip = $connection->httpRequest->getHeader('X-Forwarded-For') ? $connection->httpRequest->getHeader('X-Forwarded-For')[0] : $connection->remoteAddress;
                /** @noinspection PhpUndefinedFieldInspection */
                ChristmasContainer::getLogger()->addLog(Logger::CLIENT, 'Client disconnected', $ip, $connection->resourceId);
                return $value;
            }
        }

        return false;
    }

    /**
     * @param $msg
     * @param ConnectionInterface|null $connectionInvoker
     */
    public function broadcastMessage($msg, ConnectionInterface $connectionInvoker = null)
    {
        foreach($this->array as $connection) {
            if($connection == $connectionInvoker && $connection) continue;
            $connection->send($msg);
        }
    }
}