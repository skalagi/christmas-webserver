<?php

namespace Syntax\Service\UC;

class LED
{
    /**
     * @var string
     */
    private $color = '0.0.0';

    /**
     * @var AVRService
     */
    private $avr;

    /**
     * LED constructor.
     * @param AVRService $avr
     */
    public function __construct(AVRService $avr)
    {
        $this->avr = $avr;
    }

    /**
     * @return array
     *
     * @see list($r, $g, $b)
     */
    public function getColor()
    {
        return explode('.', $this->color);
    }

    /**
     * @param int $r
     * @param int $g
     * @param int $b
     * @return LED
     * @throws \Syntax\Exception\AVRException
     */
    public function setColor($r, $g, $b)
    {
        $this->color = sprintf('%s.%s.%s', $r, $g, $b);
        $this->avr->send('L'.$this->color);
        return $this;
    }
}