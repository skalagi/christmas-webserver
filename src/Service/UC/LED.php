<?php

namespace Syntax\Service\UC;

class LED extends AbstractUC
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

        file_put_contents(__DIR__.'/../../../current.color', str_replace('.', ',', $this->color));

        return $this;
    }
}