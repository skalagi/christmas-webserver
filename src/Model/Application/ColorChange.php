<?php

namespace Syntax\Model\Application;

use Ratchet\ConnectionInterface;

class ColorChange
{
    /**
     * @var array
     */
    public $rgb = [];

    /**
     * @var int|null
     */
    public $duration;

    /**
     * @var int
     */
    public $uid;

    /**
     * @var string
     */
    public $ip;
    
    /**
     * @var ConnectionInterface
     */
    public $connection;
}