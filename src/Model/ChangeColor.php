<?php

namespace Syntax\Model;

class ChangeColor extends AbstractModelWithStatus
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $r;

    /**
     * @var int
     */
    public $g;

    /**
     * @var int
     */
    public $b;

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