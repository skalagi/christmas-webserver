<?php

namespace Syntax\Model;

class AbstractModelWithStatus
{
    /**
     * @var string
     */
    public $status;

    const STATUS_ADDED = 'added';
    const STATUS_EXECUTED = 'executed';
}