<?php

namespace Syntax\Model\Application;

class LogEntity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var \DateTime
     */
    public $createdTime;

    /**
     * @var string
     */
    public $initiator;

    /**
     * @var array
     */
    public $data = [];
}