<?php

namespace Syntax\Exception;

class WSException extends \Exception
{
    /**
     * @var bool
     */
    public $closeConnection = false;
}