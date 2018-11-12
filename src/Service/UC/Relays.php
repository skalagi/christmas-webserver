<?php

namespace Syntax\Service\UC;

class Relays
{
    /**
     * @var array|bool[]
     */
    private $states = [false, false, false, false];

    /**
     * @var AVRService
     */
    private $avr;

    /**
     * Relays constructor.
     * @param AVRService $avr
     */
    public function __construct(AVRService $avr)
    {
        $this->avr = $avr;
    }

    /**
     * @param $channel
     * @param $value
     * @return Relays
     * @throws \Syntax\Exception\AVRException
     */
    public function setState($channel, $value)
    {
        $this->states[$channel] = $value;
        $this->avr->send('R'.$this->toByte());
        return $this;
    }

    /**
     * @return array|bool[]
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * @return int
     */
    public function toByte()
    {
        $result = 0;

        $weights = [1, 2, 4, 8];
        foreach($this->states as $i => $state) {
            if($state) {
                $result += $weights[$i];
            }
        }

        return $result;
    }
}