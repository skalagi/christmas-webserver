<?php

namespace Syntax\Service\UC;

class Relays extends AbstractUC
{
    /**
     * @var array|bool[]
     */
    private $states = [false, false, false];

    /**
     * @param $channel
     * @param $value
     * @return Relays
     * @throws \Syntax\Exception\AVRException
     */
    public function setState($channel, $value)
    {
        $this->states[$channel] = $value;

        $this->_execute(__DIR__.'/../../../rpi/write_relays.sh '.$this->_createExecutorParameters());

        return $this;
    }

    /**
     * @return string
     */
    private function _createExecutorParameters()
    {
        $parameters = '';
        foreach($this->states as $state) {
            $parameters .= ' '.($state ? '0' : '1');
        }

        return trim($parameters);
    }


    /**
     * @return array|bool[]
     */
    public function getStates()
    {
        return $this->states;
    }
}