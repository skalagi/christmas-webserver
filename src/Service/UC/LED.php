<?php

namespace Syntax\Service\UC;

class LED
{
    /**
     * @var string
     */
    private $color = '0.0.0';

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
     */
    public function setColor($r, $g, $b)
    {
        $this->color = sprintf('%s.%s.%s', $r, $g, $b);

        // TODO: Implements execute of change color

        return $this;
    }
}