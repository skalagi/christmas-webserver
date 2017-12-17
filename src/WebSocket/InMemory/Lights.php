<?php

namespace Syntax\WebSocket\InMemory;

use Syntax\Exception\AVRException;
use Syntax\Service\WebSocket\AVRService;

class Lights
{
    /**
     * @var array|bool[]
     */
    private $storage = [false, false, false, false];

    /**
     * @var AVRService
     */
    private $avr;

    /**
     * Lights constructor.
     * @param AVRService $avr
     * @throws AVRException
     */
    public function __construct(AVRService $avr)
    {
        $this->avr = $avr;
    }


    /**
     * @param int $offset
     * @return bool
     */
    public function getState($offset)
    {
        return $this->storage[$offset];
    }

    /**
     * @param int $offset
     * @param bool $value
     * @return $this
     */
    public function changeState($offset, $value)
    {
        $this->storage[$offset] = (bool)$value;
        $this->avr->send('P'.$this->toByte());
        return $this;
    }

    /**
     * @param int $byte
     * @return $this
     */
    public function setByte($byte)
    {
        $i=0;
        foreach(str_split(sprintf('%04d', decbin($byte))) as $bit) {
            $this->storage[$i++] = (bool)$bit;
        }
        $this->avr->send('P'.$this->toByte());
        return $this;
    }

    /**
     * @return array|\bool[]
     * @return null|int
     */
    public function states()
    {
        return $this->storage;
    }

    /**
     * @return int
     */
    public function toByte()
    {
        $convertedByte = 0;
        $weight = [8, 4, 2, 1];
        foreach($this->storage as $i => $bit) {
            if($bit) $convertedByte += $weight[$i];
        }
        return $convertedByte;
    }
}