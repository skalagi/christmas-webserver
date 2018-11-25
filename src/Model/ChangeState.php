<?php

namespace Syntax\Model;

class ChangeState extends AbstractModelWithStatus
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $channel;

    /**
     * @var bool
     */
    public $state;

    /**
     * ChangeState constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        foreach($fields as $name => $value) {
            $this->{$name} = $value;
        }
    }
}